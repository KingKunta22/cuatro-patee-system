<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Get delivered POs that haven't been added to inventory yet
        $unaddedPOs = PurchaseOrder::where('orderStatus', 'Delivered')
            ->whereHas('items', function($query) {
                $query->whereDoesntHave('inventory');
            })
            ->select('id', 'orderNumber')
            ->get();

        // Get all delivered POs
        $deliveredPOs = PurchaseOrder::where('orderStatus', 'Delivered')
            ->select('id', 'orderNumber')
            ->get();

        // Start query
        $query = Inventory::query();

        // Apply category filter
        if ($request->category && $request->category != 'all') {
            $query->where('productCategory', $request->category);
        }

        // Apply brand filter
        if ($request->brand && $request->brand != 'all') {
            $query->where('productBrand', $request->brand);
        }

        // Apply search filter
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('productName', 'LIKE', "%{$searchTerm}%")
                ->orWhere('productSKU', 'LIKE', "%{$searchTerm}%");
            });
        }

        $inventoryItems = $query->orderBy('created_at', 'DESC')
            ->paginate(6)
            ->withQueryString();

        // Get categories and brands from their models for dropdowns
        $categories = Category::orderBy('productCategory')->get();
        $brands = Brand::orderBy('productBrand')->get();

        // Get unique values from inventory for filters (keep your existing functionality)
        $uniqueCategories = Inventory::distinct()->pluck('productCategory')->filter();
        $uniqueBrands = Inventory::distinct()->pluck('productBrand')->filter();

        return view('inventory', compact('unaddedPOs', 'inventoryItems', 'deliveredPOs', 'categories', 'brands', 'uniqueCategories', 'uniqueBrands'));
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
        
        // Define base validation rules
        $validationRules = [];
        $fieldPrefix = '';
        
        if ($addMethod === 'manual') {
            $fieldPrefix = 'manual_';
            $validationRules = [
                'manual_productName' => 'required|string|max:255',
                'manual_productBrand' => 'required|string|max:255',
                'manual_productCategory' => 'required|string|max:255',
                'manual_productStock' => 'required|numeric|min:0',
                'manual_productSellingPrice' => 'required|numeric|min:0',
                'manual_productCostPrice' => 'required|numeric|min:0',
                'manual_productItemMeasurement' => 'required|string|max:255',
                'manual_productExpirationDate' => 'required|date|after_or_equal:today',
                'manual_productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ];
        } else {
            $fieldPrefix = '';
            $validationRules = [
                'productName' => 'required|string|max:255',
                'productBrand' => 'required|string|max:255',
                'productCategory' => 'required|string|max:255',
                'productStock' => 'required|numeric|min:0',
                'productSellingPrice' => 'required|numeric|min:0',
                'productCostPrice' => 'required|numeric|min:0',
                'productItemMeasurement' => 'required|string|max:255',
                'productExpirationDate' => 'required|date|after_or_equal:today',
                'productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'purchaseOrderNumber' => 'nullable|exists:purchase_orders,id',
                'selectedItemId' => 'nullable|exists:purchase_order_items,id',
            ];
        }

        $validated = $request->validate($validationRules);

        // Map fields to database fields using the appropriate prefix
        $inventoryData = [
            'productName' => $validated[$fieldPrefix . 'productName'],
            'productSKU' => $newSKU,
            'productBrand' => $validated[$fieldPrefix . 'productBrand'],
            'productCategory' => $validated[$fieldPrefix . 'productCategory'],
            'productStock' => $validated[$fieldPrefix . 'productStock'],
            'productSellingPrice' => $validated[$fieldPrefix . 'productSellingPrice'],
            'productCostPrice' => $validated[$fieldPrefix . 'productCostPrice'],
            'productItemMeasurement' => $validated[$fieldPrefix . 'productItemMeasurement'],
            'productExpirationDate' => $validated[$fieldPrefix . 'productExpirationDate'],
            'purchase_order_id' => ($addMethod === 'po') ? ($validated['purchaseOrderNumber'] ?? null) : null,
            'purchase_order_item_id' => ($addMethod === 'po') ? ($validated['selectedItemId'] ?? null) : null,
        ];

        // Handle image upload
        $imageField = $fieldPrefix . 'productImage';
        if ($request->hasFile($imageField)) {
            $imagePath = $request->file($imageField)->store('inventory', 'public');
            $inventoryData['productImage'] = $imagePath;
        }

        // Calculate profit margin
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

    public function update(Request $request, Inventory $inventory) 
    {
        // Validate Requests - Use string validation instead of hardcoded in: values
        $validated = $request->validate([
            'productName' => 'required|string|max:255',
            'productBrand' => 'required|string|max:255', // Changed from hardcoded in: values
            'productCategory' => 'required|string|max:255', // Changed from hardcoded in: values
            'productStock' => 'required|numeric|min:0',
            'productSellingPrice' => 'required|numeric|min:0',
            'productCostPrice' => 'required|numeric|min:0',
            'productItemMeasurement' => 'required|in:kilogram,gram,liter,milliliter,pcs,set,pair,pack',
            'productExpirationDate' => 'required|date|after_or_equal:today',
            'productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Recalculate Profit Margin
        $updatedProfitMargin = 0;
        $updatedCostPrice = $validated['productCostPrice'];
        $updatedSellingPrice = $validated['productSellingPrice'];

        if ($updatedCostPrice > 0) {
            $updatedProfitMargin = round((($updatedSellingPrice - $updatedCostPrice) / $updatedCostPrice) * 100, 2);
        }

        // Update the fields
        $inventory->update([
            'productName' => $validated['productName'],
            'productBrand' => $validated['productBrand'],
            'productCategory' => $validated['productCategory'],
            'productStock' => $validated['productStock'],
            'productSellingPrice' => $validated['productSellingPrice'],
            'productCostPrice' => $validated['productCostPrice'],
            'productItemMeasurement' => $validated['productItemMeasurement'],
            'productExpirationDate' => $validated['productExpirationDate'],
            'productProfitMargin' => $updatedProfitMargin,
        ]);

        // Handle new file uploads
        if ($request->hasFile('productImage')) {
            if ($inventory->productImage) {
                Storage::disk('public')->delete($inventory->productImage);
            }

            $newImagePath = $request->file('productImage')->store('inventory', 'public');
            $inventory->update(['productImage' => $newImagePath]);
        }

        return redirect()->route('inventory.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Inventory $inventory)
    {
        if ($inventory->productImage) {
            Storage::disk('public')->delete($inventory->productImage);
        }

        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', 'Inventory product successfully deleted!');
    }

}
