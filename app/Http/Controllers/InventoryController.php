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
        // Get delivered POs that haven't been added to inventory yet (UPDATED)
        $unaddedPOs = PurchaseOrder::whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->whereHas('items', function($query) {
                $query->whereDoesntHave('productBatches'); // Changed from 'inventory'
            })
            ->select('id', 'orderNumber')
            ->get();

        // Get all delivered POs (for reference if needed)
        $deliveredPOs = PurchaseOrder::whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->select('id', 'orderNumber')
            ->get();

        // Start query
        $query = Product::with(['batches', 'brand', 'category']);

        // Apply category filter (UPDATED for relational structure)
        if ($request->category && $request->category != 'all') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('productCategory', $request->category);
            });
        }

        // Apply brand filter (UPDATED for relational structure)
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
        return PurchaseOrderItem::where('purchase_order_id', $poId)
            ->whereDoesntHave('productBatches') // Changed from 'inventory'
            ->select('id', 'productName', 'quantity', 'unitPrice', 'itemMeasurement')
            ->get();
    }
        

    public function store(Request $request)
    {
        // Determine which method will be used first
        $addMethod = $request->input('add_method');
        
        // Remove unwanted fields based on the method
        if ($addMethod === 'manual') {
            // Remove PO fields for manual mode
            $request->request->remove('productStock');
            $request->request->remove('display_productStock');
            $request->request->remove('purchaseOrderNumber');
            $request->request->remove('selectedItemId');
            $request->request->remove('productName');
            $request->request->remove('productBrand');
            $request->request->remove('productCategory');
            $request->request->remove('productSellingPrice');
            $request->request->remove('productCostPrice');
            $request->request->remove('productItemMeasurement');
            $request->request->remove('productImage');
            $request->request->remove('productQuality');
            $request->request->remove('badItemQuantity');
            $request->request->remove('batches');
        } else {
            // Remove manual fields for PO mode
            $request->request->remove('manual_productName');
            $request->request->remove('manual_productBrand');
            $request->request->remove('manual_productCategory');
            $request->request->remove('manual_productStock');
            $request->request->remove('manual_productSellingPrice');
            $request->request->remove('manual_productCostPrice');
            $request->request->remove('manual_productItemMeasurement');
            $request->request->remove('manual_productImage');
            $request->request->remove('manual_batches');
        }
        
        // Define validation rules
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
                'manual_productStock' => 'required|numeric|min:1',
                'manual_productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'manual_batches' => 'required|array|min:1',
                'manual_batches.*.quantity' => 'required|numeric|min:1',
                'manual_batches.*.expiration_date' => 'required|date|after_or_equal:today',
            ];
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
                'productQuality' => 'required|in:goodCondition,defective,incorrectItem,nearExpiry,rejected,quantityMismatch',
                'badItemQuantity' => 'nullable|numeric|min:0',
                'batches' => 'required|array|min:1',
                'batches.*.quantity' => 'required|numeric|min:1',
                'batches.*.expiration_date' => 'required|date|after_or_equal:today',
            ];
        }

        // dd($request->all());
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

        // Generate SKU based on product details
        $productName = $validated[$fieldPrefix . 'productName'];
        $productBrand = $validated[$fieldPrefix . 'productBrand'];
        $productCategory = $validated[$fieldPrefix . 'productCategory'];
        $newSKU = $this->generateSKU($productName, $productBrand, $productCategory);

        // Start transaction
        DB::beginTransaction();

        try {
            // Find or create brand and category
            $brand = Brand::firstOrCreate(['productBrand' => $productBrand]);
            $category = Category::firstOrCreate(['productCategory' => $productCategory]);

            // Prepare product data using foreign keys, not text fields
            $productData = [
                'productName' => $productName,
                'productSKU' => $newSKU,
                'brand_id' => $brand->id,           // ← Foreign key
                'category_id' => $category->id,     // ← Foreign key
                'productItemMeasurement' => $validated[$fieldPrefix . 'productItemMeasurement'],
                'productSellingPrice' => $validated[$fieldPrefix . 'productSellingPrice'],
                'productCostPrice' => $validated[$fieldPrefix . 'productCostPrice'],
            ];

            // Handle image upload
            $imageField = $fieldPrefix . 'productImage';
            if ($request->hasFile($imageField)) {
                $productData['productImage'] = $request->file($imageField)->store('products', 'public');
            }

            // Create or find product
            $product = Product::firstOrCreate(
                ['productSKU' => $newSKU],
                $productData
            );

            // Create batches
            $batchesField = $fieldPrefix . 'batches';
            foreach ($validated[$batchesField] as $batchIndex => $batch) {
                $batchNumber = 'BATCH-' . date('Ymd') . '-' . str_pad(($batchIndex + 1), 3, '0', STR_PAD_LEFT);
                
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

            // For PO method, handle bad items
            if ($addMethod === 'po' && isset($validated['productQuality']) && $validated['productQuality'] !== 'goodCondition') {
                $badItemCount = $validated['badItemQuantity'] ?? 0;
                if ($badItemCount > 0) {
                    BadItem::create([
                        'purchase_order_item_id' => $validated['selectedItemId'],
                        'quality_status' => $validated['productQuality'],
                        'item_count' => $badItemCount,
                        'notes' => 'Added via inventory system'
                    ]);
                }
            }

            DB::commit();
            
            return redirect()->route('inventory.index')->with('success', 'Product added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->withErrors([
                'error' => 'Failed to save product: ' . $e->getMessage()
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
