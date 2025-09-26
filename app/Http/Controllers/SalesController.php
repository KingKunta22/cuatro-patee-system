<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class SalesController extends Controller
{
    // Show all sales
    public function index()
    {
        // Total Revenue (based on selling price)
        $totalRevenue = SaleItem::sum(DB::raw('quantity * unit_price'));
        
        // Total Cost - ONLY from purchase orders with DELIVERED status
        $totalCost = SaleItem::join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('purchase_orders', 'product_batches.purchase_order_id', '=', 'purchase_orders.id')
            ->join('deliveries', 'purchase_orders.id', '=', 'deliveries.purchase_order_id')
            ->where('deliveries.orderStatus', 'Delivered') // ← CRITICAL FIX
            ->sum(DB::raw('sale_items.quantity * product_batches.cost_price'));
        
        // Total Profit
        $totalProfit = $totalRevenue - $totalCost;
        
        // Get sales data
        $sales = Sale::with('items.productBatch.product')->latest()->paginate(7);
        
        // Get products for the product dropdown
        $products = Product::with([
                'brand', // Add this line to load the brand relationship
                'batches' => function($query) {
                    $query->where('quantity', '>', 0)
                        ->orderBy('expiration_date', 'asc');
                }
            ])
            ->whereHas('batches', function($query) {
                $query->where('quantity', '>', 0);
            })
            ->get()
            ->map(function($product) {
                $product->total_stock = $product->batches->sum('quantity');
                return $product;
            });

        return view('sales', compact('sales', 'totalRevenue', 'totalCost', 'totalProfit', 'products'));
    }

    // Generate sequential invoice number in MDYY format
    private function generateInvoiceNumber()
    {
        $prefix = 'INV-';
        $date = now()->format('md'); // MD format (month and day)
        $year = now()->format('y'); // Last 2 digits of year
        
        // Get the highest sequential number for today
        $latestInvoice = Sale::where('invoice_number', 'like', $prefix . $date . $year . '-%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($latestInvoice) {
            $parts = explode('-', $latestInvoice->invoice_number);
            $lastPart = end($parts);
            
            if (preg_match('/^\d{3}$/', $lastPart)) {
                $sequence = (int) $lastPart + 1;
            } else {
                $sequence = 1;
            }
        } else {
            $sequence = 1;
        }

        $sequenceFormatted = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        return $prefix . $date . $year . '-' . $sequenceFormatted; // INV-092325-001 format
    }

    // Store a new sale
    public function store(Request $request)
    {
        $validated = $request->validate([
            'salesCash' => 'required|numeric|min:0',
        ]);

        // Validate the cart items
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Start a database transaction for safety
        DB::beginTransaction();

        try {
            // Generate sequential invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Calculate totals
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $cashReceived = $request->salesCash;
            $change = max(0, $cashReceived - $totalAmount);

            // Create the sale - use a default customer name or empty
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'sale_date' => now(),
                // 'customer_name' => 'Walk-in Customer', Or $request->customerName
                'total_amount' => $totalAmount,
                'cash_received' => $cashReceived,
                'change' => $change,
                'user_id' => Auth::id(), // Store the logged-in user

            ]);

            // Process each item in the cart
            foreach ($request->items as $item) {
                $productBatch = ProductBatch::findOrFail($item['product_batch_id']);

                // Check stock availability
                if ($productBatch->quantity < $item['quantity']) {
                    throw new \Exception("Not enough stock for batch {$productBatch->batch_number}. Available: {$productBatch->quantity}");
                }

                // Update batch stock
                $productBatch->decrement('quantity', $item['quantity']);

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_batch_id' => $item['product_batch_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price']
                ]);
            }

            // Commit the transaction
            DB::commit();

            // Post-save behavior: set session flags for download/print and redirect with success
            return redirect()->route('sales.index')
                ->with('success', 'Sale completed successfully!')
                ->with('download_sale_id', $request->boolean('salesDownload') ? $sale->id : null);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

        // PDF Download method
    public function downloadReceipt($saleId)
    {
        $sale = Sale::with(['items.productBatch'])->findOrFail($saleId);
        
        $pdf = Pdf::loadView('pdf.receipt', compact('sale'));
        $filename = 'receipt-' . $sale->invoice_number . '.pdf';

        // Stream inline if requested (for print view), else force download
        if (request()->boolean('inline')) {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }

    // PDF Download for existing sales (from view details)
    public function downloadSaleReceipt($saleId)
    {
        return $this->downloadReceipt($saleId);
    }


    // Edit method - Show edit form
    public function edit($id)
    {
        $sale = Sale::with(['items.inventory', 'customer'])->findOrFail($id);
        
        return view('sales.edit', compact('sale', 'customers'));
    }

    // Update method - Process edit form
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        
        // Validate request
        $validated = $request->validate([
            'customerName' => 'required|string|max:255',
            'sale_date' => 'required|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);
        
        // Update sale details
        $sale->update([
            'customer_name' => $validated['customerName'],
            'sale_date' => $validated['sale_date']
        ]);
        
        // Update sale items and handle deletions
        $totalAmount = 0;
        
        if ($request->has('items')) {
            foreach ($request->items as $itemId => $itemData) {
                // Check if item is marked for deletion
                if (isset($itemData['_delete']) && $itemData['_delete'] == '1') {
                    $saleItem = SaleItem::find($itemId);
                    if ($saleItem && $saleItem->sale_id == $sale->id) {
                        $saleItem->delete();
                    }
                    continue;
                }
                
                $saleItem = SaleItem::find($itemId);
                if ($saleItem && $saleItem->sale_id == $sale->id) {
                    $saleItem->update([
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['quantity'] * $itemData['unit_price']
                    ]);
                    
                    $totalAmount += $saleItem->total_price;
                }
            }
        }
        
        // Update sale total
        $sale->update(['total_amount' => $totalAmount]);
        
        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully!');
    }

    // Destroy method - Delete sale
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        
        // Delete associated items first
        $sale->items()->delete();
        
        // Delete the sale
        $sale->delete();
        
        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully!');
    }

}