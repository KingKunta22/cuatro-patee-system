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

        // Generate a new SKU for the form
        $newSKU = $this->generateSKU();

        return view('inventory', compact('deliveredPOs', 'newSKU'));
    }


    public function getItems($poId)
    {
        return PurchaseOrderItem::where('purchase_order_id', $poId) // Finds items where purchase_order_id matches the selected PO
            ->select('id', 'productName', 'quantity', 'unitPrice', 'itemMeasurement') // Ensures these fields exist first
            ->get();
    }
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'productName' => 'required|string|max:255',
            'productSKU' => 'required|string|max:255',
            'productBrand' => 'required|in:Pedigree,Whiskas,Royal Canin,Cesar,Acana',
            'productCategory' => 'required|string|max:255',
            'productStock' => 'required|numeric|min:0',
            'productSellingPrice' => 'required|numeric|min:0',
            'productCostPrice' => 'required|numeric|min:0',
            'itemMeasurement' => 'required|in:kilogram,gram,liter,milliliter,pcs,set,pair,pack',
            'productExpDate' => 'required|date|after:today',
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
            'productItemMeasurement' => $validated['itemMeasurement'],
            'productExpirationDate' => $validated['productExpDate'],
            'productImage' => $validated['image'] ?? null,
            'purchase_order_id' => $validated['purchaseOrderNumber'] ?? null,
            'purchase_order_item_id' => $validated['selectedItemId'] ?? null,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Product added!');
    }


    private function generateSKU()
    {
        // Generate a unique SKU with format: INV-YYYYMMDD-XXXX
        $date = now()->format('Ymd'); // Gets current date in YYYYMMDD format
        $lastInventory = Inventory::whereDate('created_at', today())->count(); // Count how many items were created today
        $sequence = str_pad($lastInventory + 1, 4, '0', STR_PAD_LEFT); // Creates a 4-digit sequence number (0001, 0002, etc.)
        
        return "INV-{$date}-{$sequence}"; // INV-20241220-0001
    }
}
