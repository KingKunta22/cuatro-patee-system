<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BadItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Get delivered POs that haven't been added to inventory yet
        $unaddedPOs = PurchaseOrder::whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->whereHas('items', function($query) {
                $query->whereDoesntHave('inventory');
            })
            ->select('id', 'orderNumber')
            ->get();

        // Get all delivered POs (for reference if needed)
        $deliveredPOs = PurchaseOrder::whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->select('id', 'orderNumber')
            ->get();

        // Start query with relationships
        $query = Product::with(['batches', 'brand', 'category']); // Add brand and category relationships

        // Apply category filter - need to join with categories table
        if ($request->category && $request->category != 'all') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('productCategory', $request->category);
            });
        }

        // Apply brand filter - need to join with brands table
        if ($request->brand && $request->brand != 'all') {
            $query->whereHas('brand', function($q) use ($request) {
                $q->where('productBrand', $request->brand);
            });
        }

        // Apply search filter
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('productName', 'LIKE', "%{$searchTerm}%")
                ->orWhere('productSKU', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Get products instead of inventory items
        $products = $query->paginate(7)->withQueryString();

        // Get categories and brands from their models for dropdowns
        $categories = Category::orderBy('productCategory')->get();
        $brands = Brand::orderBy('productBrand')->get();

        return view('inventory', compact(
            'unaddedPOs', 
            'products', 
            'deliveredPOs', 
            'categories', 
            'brands', 
        ));
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
    // Determine which method will be used first
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
            'manual_productItemMeasurement' => 'required|string|max:255',
            'manual_productSellingPrice' => 'required|numeric|min:0',
            'manual_productCostPrice' => 'required|numeric|min:0',
            'manual_productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'manual_batches' => 'required|array|min:1',
            'manual_batches.*.quantity' => 'required|numeric|min:1',
            'manual_batches.*.expiration_date' => 'required|date|after_or_equal:today',
        ];
        
        // Add expiration date validation only if no batches are provided
        if (empty($request->input('manual_batches'))) {
            $validationRules['manual_productExpirationDate'] = 'required|date|after_or_equal:today';
        }
        
        // Remove PO field validations for manual mode
        $request->request->remove('purchaseOrderNumber');
        $request->request->remove('selectedItemId');
        $request->request->remove('productName');
        $request->request->remove('productBrand');
        $request->request->remove('productCategory');
        $request->request->remove('productSellingPrice');
        $request->request->remove('productCostPrice');
        $request->request->remove('productItemMeasurement');
        $request->request->remove('productExpirationDate');
        $request->request->remove('productImage');
        $request->request->remove('productQuality');
        $request->request->remove('badItemQuantity');
        
    } else {
        $fieldPrefix = '';
        $validationRules = [
            'productName' => 'required|string|max:255',
            'productBrand' => 'required|string|max:255',
            'productCategory' => 'required|string|max:255',
            'productItemMeasurement' => 'required|string|max:255',
            'productSellingPrice' => 'required|numeric|min:0',
            'productCostPrice' => 'required|numeric|min:0',
            'productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'purchaseOrderNumber' => 'required|exists:purchase_orders,id',
            'selectedItemId' => 'required|exists:purchase_order_items,id',
            'batches' => 'required|array|min:1',
            'batches.*.quantity' => 'required|numeric|min:1',
            'batches.*.expiration_date' => 'required|date|after_or_equal:today',
        ];
        
        // Remove manual field validations for PO mode
        $request->request->remove('manual_productName');
        $request->request->remove('manual_productBrand');
        $request->request->remove('manual_productCategory');
        $request->request->remove('manual_productStock');
        $request->request->remove('manual_productSellingPrice');
        $request->request->remove('manual_productCostPrice');
        $request->request->remove('manual_productItemMeasurement');
        $request->request->remove('manual_productExpirationDate');
        $request->request->remove('manual_productImage');
        $request->request->remove('manual_batches');
    }


    $validated = $request->validate($validationRules);

    // For manual method with batches, validate that total batches equal total stock
    if ($addMethod === 'manual' && isset($validated['manual_batches'])) {
        $totalBatchQuantity = array_sum(array_column($validated['manual_batches'], 'quantity'));
        if ($totalBatchQuantity != $validated['manual_productStock']) {
            return back()->withInput()->withErrors([
                'error' => 'Total batch quantities must equal total stock count'
            ]);
        }
    }

    // For PO method, calculate actual stock FIRST
    $poItem = null;
    if ($addMethod === 'po') {
        $poItem = PurchaseOrderItem::find($validated['selectedItemId']);
        
        // Validate PO item belongs to selected PO
        if (!$poItem || $poItem->purchase_order_id != $validated['purchaseOrderNumber']) {
            return back()->withInput()->withErrors([
                'error' => 'Selected item does not belong to the chosen purchase order'
            ]);
        }
        
        $totalQuantity = $poItem->quantity;
        $badItemCount = ($validated['productQuality'] !== 'goodCondition') 
            ? ($validated['badItemQuantity'] ?? 0) 
            : 0;
        
        $actualStock = $totalQuantity - $badItemCount;
        
        if ($actualStock < 0) {
            return back()->withInput()->withErrors([
                'error' => 'Bad items cannot exceed total quantity'
            ]);
        }
        
        if ($actualStock === 0) {
            return back()->withInput()->withErrors([
                'error' => 'Cannot add product with zero stock'
            ]);
        }
    }

    // Generate SKU based on product details (AFTER validation)
    $productName = $validated[$fieldPrefix . 'productName'];
    $productBrand = $validated[$fieldPrefix . 'productBrand'];
    $productCategory = $validated[$fieldPrefix . 'productCategory'];

    // For manual method with batches, use the first batch's expiration date
    if ($addMethod === 'manual' && isset($validated['manual_batches']) && count($validated['manual_batches']) > 0) {
        $validated[$fieldPrefix . 'productExpirationDate'] = $validated['manual_batches'][0]['expiration_date'];
    }

    $newSKU = $this->generateSKU($productName, $productBrand, $productCategory);
    
    // For non-manual methods, check if batch already exists
    if ($addMethod !== 'manual') {
        $existingBatch = Inventory::where('productSKU', $newSKU)
            ->where('productExpirationDate', $validated[$fieldPrefix . 'productExpirationDate'])
            ->first();
        
        if ($existingBatch) {
            $stockToAdd = ($addMethod === 'po') ? $actualStock : $validated[$fieldPrefix . 'productStock'];
            $existingBatch->update([
                'productStock' => $existingBatch->productStock + $stockToAdd
            ]);
            
            return redirect()->route('inventory.index')->with('success', 'Product stock updated successfully!');
        }
    }

    // Start transaction
    DB::beginTransaction();

    try {
        // Generate SKU
        $productName = $validated[$fieldPrefix . 'productName'];
        $productBrand = $validated[$fieldPrefix . 'productBrand'];
        $productCategory = $validated[$fieldPrefix . 'productCategory'];
        $newSKU = $this->generateSKU($productName, $productBrand, $productCategory);

        // First find the brand and category IDs
        $brand = Brand::where('productBrand', $productBrand)->first();
        $category = Category::where('productCategory', $productCategory)->first();

        $productData = [
            'productName' => $productName,
            'productSKU' => $newSKU,
            'brand_id' => $brand->id ?? null, // Use foreign key
            'category_id' => $category->id ?? null, // Use foreign key
            'productItemMeasurement' => $validated[$fieldPrefix . 'productItemMeasurement'],
            'productSellingPrice' => $validated[$fieldPrefix . 'productSellingPrice'],
            'productCostPrice' => $validated[$fieldPrefix . 'productCostPrice'],
        ];

        // Handle image upload
        $imageField = $fieldPrefix . 'productImage';
        if ($request->hasFile($imageField)) {
            $productData['productImage'] = $request->file($imageField)->store('products', 'public');
        }

        $product = Product::firstOrCreate(
            ['productSKU' => $newSKU],
            $productData
        );

        // Create batches
        $batchesField = $fieldPrefix . 'batches';
        foreach ($validated[$batchesField] as $batchIndex => $batch) {
            $batchNumber = date('Ymd') . '-' . str_pad(($batchIndex + 1), 3, '0', STR_PAD_LEFT);
            
            $batchData = [
                'product_id' => $product->id,
                'batch_number' => $batchNumber,
                'quantity' => $batch['quantity'],
                'cost_price' => $validated[$fieldPrefix . 'productCostPrice'],
                'selling_price' => $validated[$fieldPrefix . 'productSellingPrice'],
                'expiration_date' => $batch['expiration_date'],
            ];

            // Add PO references for PO method
            if ($addMethod === 'po') {
                $batchData['purchase_order_id'] = $validated['purchaseOrderNumber'];
                $batchData['purchase_order_item_id'] = $validated['selectedItemId'];
            }

            ProductBatch::create($batchData);
        }

        DB::commit();
        return redirect()->route('inventory.index')->with('success', 'Product added successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors([
            'error' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}


    private function generateSKU($productName, $productBrand, $productCategory)
    {
        // Create a consistent base SKU
        $base = substr(strtoupper(preg_replace('/[^a-z0-9]/i', '', $productName)), 0, 3);
        $brand = substr(strtoupper(preg_replace('/[^a-z0-9]/i', '', $productBrand)), 0, 2);
        $category = substr(strtoupper(preg_replace('/[^a-z0-9]/i', '', $productCategory)), 0, 2);
        
        return "{$base}-{$brand}-{$category}";
    }

    public function update(Request $request, Inventory $inventory) 
    {
        // Validate Requests - Use string validation instead of hardcoded in: values
        $validated = $request->validate([
            'productName' => 'required|string|max:255',
            'productBrand' => 'required|string|max:255', 
            'productCategory' => 'required|string|max:255', 
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

        $updatedProfitMargin = round($updatedSellingPrice - $updatedCostPrice, 2);


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
