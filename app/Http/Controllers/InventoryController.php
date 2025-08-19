<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class InventoryController extends Controller
{
    public function index()
    {
        $deliveredPOs = PurchaseOrder::where('orderStatus', 'Delivered')
                    ->select('id', 'orderNumber')
                    ->get();

        $incomingPOs = PurchaseOrder::where('orderStatus', 'Confirmed')
                    ->select('id', 'orderNumber')
                    ->get();

        // Generate a new SKU for the form
        $newSKU = $this->generateSKU();

        $inventoryItems = Inventory::orderBy('id', 'DESC')
                    ->paginate(6)
                    ->withQueryString();

        return view('inventory', compact('deliveredPOs', 'incomingPOs', 'newSKU', 'inventoryItems'));
    }


    public function getItems($poId)
    {
        return PurchaseOrderItem::where('purchase_order_id', $poId) // Finds items where purchase_order_id matches the selected PO
            ->whereDoesntHave('inventory') // Excludes items that are already added/linked
            ->select('id', 'productName', 'quantity', 'unitPrice', 'itemMeasurement') // Ensures these fields exist first
            ->get();
    }
    

    public function store(Request $request)
    {
        dd($request->all()); // Debug the incoming request
        
        $validated = $request->validate([
            'productName' => 'required|string|max:255',
            'productSKU' => 'required|string|max:255',
            'productBrand' => 'required|in:Pedigree,Whiskas,Royal Canin,Cesar,Acana',
            'productCategory' => 'required|string|max:255',
            'productStock' => 'required|numeric|min:0',
            'productSellingPrice' => 'required|numeric|min:0',
            'productCostPrice' => 'required|numeric|min:0',
            'productItemMeasurement' => 'required|in:kilogram,gram,liter,milliliter,pcs,set,pair,pack',
            'productExpirationDate' => 'required|date|after:today',
            'productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'purchaseOrderNumber' => 'nullable|exists:purchase_orders,id',
            'selectedItemId' => 'nullable|exists:purchase_order_items,id',
        ]);

        // Calculate profit margin
        $profitMargin = 0;
        if ($validated['productCostPrice'] > 0) {
            $profitMargin = round(($validated['productSellingPrice'] - $validated['productCostPrice']) / $validated['productCostPrice'] * 100, 2);
        }

        // Handle image upload
        if ($request->hasFile('productImage')) {
            $imagePath = $request->file('productImage')->store('inventory', 'public');
            $validated['image'] = $imagePath;
        }

        // Save to database
        Inventory::create([
            'productName' => $validated['productName'],
            'productSKU' => $validated['productSKU'],
            'productBrand' => $validated['productBrand'],
            'productCategory' => $validated['productCategory'],
            'productStock' => $validated['productStock'],
            'productSellingPrice' => $validated['productSellingPrice'],
            'productCostPrice' => $validated['productCostPrice'],
            'productProfitMargin' => $profitMargin,
            'productItemMeasurement' => $validated['productItemMeasurement'],
            'productExpirationDate' => $validated['productExpirationDate'],
            'productImage' => $validated['image'] ?? null,
            'purchase_order_id' => $validated['purchaseOrderNumber'] ?? null,
            'purchase_order_item_id' => $validated['selectedItemId'] ?? null,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Product added successfully!');
    }


    private function generateSKU()
    {
        // Generate a unique SKU with format: INV-YYYYMMDD-XXXX
        $date = now()->format('Ym'); // Gets current date in YYYYMMDD format
        $lastInventory = Inventory::whereYear('created_at', now()->year)
                                ->whereMonth('created_at', now()->month)
                                ->count(); // Counts items created this MONTH
        $sequence = str_pad($lastInventory + 1, 4, '0', STR_PAD_LEFT); // Creates a 4-digit sequence number (0001, 0002, etc.)
        
        return "INV-{$date}-{$sequence}"; // INV-20241220-0001
    }
}
