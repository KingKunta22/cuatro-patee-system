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
        // Get delivered POs that haven't been added to inventory yet
        $deliveredPOs = PurchaseOrder::where('orderStatus', 'Delivered')
            ->whereHas('items', function($query) {
                $query->whereDoesntHave('inventory');
            })
            ->select('id', 'orderNumber')
            ->get();

        $inventoryItems = Inventory::orderBy('id', 'DESC')
                    ->paginate(6)
                    ->withQueryString();

        return view('inventory', compact('deliveredPOs', 'inventoryItems'));
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
        // Generate SKU first
        $newSKU = $this->generateSKU();
        
        // Determine which method we're using
        $addMethod = $request->input('add_method');
        
        if ($addMethod === 'manual') {
            // Handle manual addition
            $validated = $request->validate([
                'manual_productName' => 'required|string|max:255',
                'manual_productBrand' => 'required|in:Pedigree,Whiskas,Royal Canin,Cesar,Acana',
                'manual_productCategory' => 'required|string|max:255',
                'manual_productStock' => 'required|numeric|min:0',
                'manual_productSellingPrice' => 'required|numeric|min:0',
                'manual_productCostPrice' => 'required|numeric|min:0',
                'manual_productItemMeasurement' => 'required|in:kilogram,gram,liter,milliliter,pcs,set,pair,pack',
                'manual_productExpirationDate' => 'required|date|after:today',
                'manual_productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Map manual fields to database fields
            $inventoryData = [
                'productName' => $validated['manual_productName'],
                'productSKU' => $newSKU, // Use the generated SKU
                'productBrand' => $validated['manual_productBrand'],
                'productCategory' => $validated['manual_productCategory'],
                'productStock' => $validated['manual_productStock'],
                'productSellingPrice' => $validated['manual_productSellingPrice'],
                'productCostPrice' => $validated['manual_productCostPrice'],
                'productItemMeasurement' => $validated['manual_productItemMeasurement'],
                'productExpirationDate' => $validated['manual_productExpirationDate'],
                'purchase_order_id' => null,
                'purchase_order_item_id' => null,
            ];

            // Handle image upload for manual
            if ($request->hasFile('manual_productImage')) {
                $imagePath = $request->file('manual_productImage')->store('inventory', 'public');
                $inventoryData['productImage'] = $imagePath;
            }

        } else {
            // Handle PO addition
            $validated = $request->validate([
                'productName' => 'required|string|max:255',
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

            // Use PO fields directly
            $inventoryData = [
                'productName' => $validated['productName'],
                'productSKU' => $newSKU, // Use the generated SKU
                'productBrand' => $validated['productBrand'],
                'productCategory' => $validated['productCategory'],
                'productStock' => $validated['productStock'],
                'productSellingPrice' => $validated['productSellingPrice'],
                'productCostPrice' => $validated['productCostPrice'],
                'productItemMeasurement' => $validated['productItemMeasurement'],
                'productExpirationDate' => $validated['productExpirationDate'],
                'purchase_order_id' => $validated['purchaseOrderNumber'] ?? null,
                'purchase_order_item_id' => $validated['selectedItemId'] ?? null,
            ];

            // Handle image upload for PO
            if ($request->hasFile('productImage')) {
                $imagePath = $request->file('productImage')->store('inventory', 'public');
                $inventoryData['productImage'] = $imagePath;
            }
        }

        // Calculate profit margin (common for both methods)
        $profitMargin = 0;
        if ($inventoryData['productCostPrice'] > 0) {
            $profitMargin = round(($inventoryData['productSellingPrice'] - $inventoryData['productCostPrice']) / $inventoryData['productCostPrice'] * 100, 2);
        }
        $inventoryData['productProfitMargin'] = $profitMargin;

        // Save to database
        Inventory::create($inventoryData);

        return redirect()->route('inventory.index')->with('success', 'Product added successfully!');
    }


    private function generateSKU()
    {
        // Generate a unique SKU with format: INV-YYYYMM-XXXX
        $date = now()->format('Ym'); // Gets current date in YYYYMM format
        $lastInventory = Inventory::where('productSKU', 'like', "INV-{$date}-%")
                                ->orderBy('productSKU', 'desc')
                                ->first();
        
        if ($lastInventory) {
            // Extract the sequence number from the last SKU
            $lastSequence = (int) substr($lastInventory->productSKU, -4);
            $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "INV-{$date}-{$sequence}";
    }

    // DELETE PRODUCT FROM INVENTORY
    public function destroy(Inventory $inventory){
        $inventory->delete();

        return redirect()->back();
    }


}
