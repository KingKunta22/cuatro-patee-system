<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesController extends Controller
{
    // Show all sales
    public function index()
    {
        // Total Revenue (based on selling price)
        $totalRevenue = SaleItem::sum(DB::raw('quantity * unit_price'));
        
        // Total Cost (based on cost price from product batches)
        $totalCost = SaleItem::join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->sum(DB::raw('sale_items.quantity * product_batches.cost_price'));
        
        // Total Profit (revenue minus cost)
        $totalProfit = $totalRevenue - $totalCost;
        
        // Get sales data
        $sales = Sale::with('items.productBatch.product')->latest()->paginate(7);
        
        // Get products for the product dropdown
        $products = Product::with(['batches' => function($query) {
                        $query->where('quantity', '>', 0) // Only batches with stock
                            ->orderBy('expiration_date', 'asc'); // FIFO/FEFO ordering
                    }])
                    ->whereHas('batches', function($query) {
                        $query->where('quantity', '>', 0); // Only products with available stock
                    })
                    ->get()
                    ->map(function($product) {
                        // Calculate total stock for display
                        $product->total_stock = $product->batches->sum('quantity');
                        return $product;
                    });

        return view('sales', compact('sales', 'totalRevenue', 'totalCost', 'totalProfit', 'products'));
    }

    // Generate sequential invoice number
    private function generateInvoiceNumber()
    {
        $prefix = 'INV-';
        $date = now()->format('Ymd');
        
        // Get the highest sequential number for today
        $latestInvoice = Sale::where('invoice_number', 'like', $prefix . $date . '-%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($latestInvoice) {
            $parts = explode('-', $latestInvoice->invoice_number);
            $lastPart = end($parts);
            
            if (preg_match('/^\d{5}$/', $lastPart)) {
                $sequence = (int) $lastPart + 1;
            } else {
                $sequence = 1;
            }
        } else {
            $sequence = 1;
        }

        $sequenceFormatted = str_pad($sequence, 5, '0', STR_PAD_LEFT);
        return $prefix . $date . '-' . $sequenceFormatted;
    }

    // Store a new sale
    public function store(Request $request)
    {
        // Validate the main form data
        $validated = $request->validate([
            'customerName' => 'required|string|max:255',
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

            // Create the sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'sale_date' => now(),
                'customer_name' => $request->customerName,
                'total_amount' => $totalAmount,
                'cash_received' => $cashReceived,
                'change' => $change
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

                // REMOVED: No need to update inventory table anymore
                // $inventory = Inventory::where('product_id', $item['product_id'])->first();
                // if ($inventory) {
                //     $inventory->decrement('total_quantity', $item['quantity']);
                // }

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

            return redirect()->route('sales.index')
                ->with('success', 'Sale completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
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