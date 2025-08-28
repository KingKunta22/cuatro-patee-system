<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    // Show all sales
    public function index()
    {
        // Calculate the stats
        $totalRevenue = Sale::sum('total_amount');
        $totalCost = SaleItem::sum(DB::raw('quantity * unit_price'));
        $totalProfit = $totalRevenue - $totalCost;
        
        // Get sales data
        $sales = Sale::with('items')->latest()->paginate(10);
        
        // Get inventories for the product dropdown
        $inventories = Inventory::all();
        
        // Get customers for the customer dropdown
        $customers = Customer::all();
        
        return view('sales', compact('sales', 'totalRevenue', 'totalCost', 'totalProfit', 'inventories', 'customers'));
    }

    // Show form to create new sale
    public function create()
    {
        $inventories = Inventory::all();
        $customers = Customer::all();
        return view('sales.create', compact('inventories', 'customers'));
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
            // Extract the sequence number properly
            $parts = explode('-', $latestInvoice->invoice_number);
            $lastPart = end($parts);
            
            // Check if the last part is a sequential number (5 digits)
            if (preg_match('/^\d{5}$/', $lastPart)) {
                $sequence = (int) $lastPart + 1;
            } else {
                // If it's a random string (like "68AF9017413D5"), start from 1
                $sequence = 1;
            }
        } else {
            // First invoice of the day
            $sequence = 1;
        }

        // Format with leading zeros: 00001, 00002, etc.
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
            'items.*.inventory_id' => 'required|exists:inventories,id',
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
                $inventory = Inventory::findOrFail($item['inventory_id']);

                // Check stock availability
                if ($inventory->productStock < $item['quantity']) {
                    throw new \Exception("Not enough stock for {$inventory->productName}. Available: {$inventory->productStock}");
                }

                // Update inventory stock
                $inventory->decrement('productStock', $item['quantity']);

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price']
                ]);
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Sale completed successfully! Invoice: ' . $invoiceNumber);

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}