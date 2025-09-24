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
                $query->whereDoesntHave('productBatches');
            })
            ->select('id', 'orderNumber')
            ->get();

        // Get all delivered POs (for reference if needed)
        $deliveredPOs = PurchaseOrder::whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->select('id', 'orderNumber')
            ->get();

        // Start query - ADD ORDER BY HERE
        $query = Product::with(['batches', 'brand', 'category'])
            ->orderBy('created_at', 'desc'); // Newest first

        // Apply category filter
        if ($request->category && $request->category != 'all') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('productCategory', $request->category);
            });
        }

        // Apply brand filter
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
                'manual_is_perishable' => 'sometimes|boolean',
                // Make batches nullable and only validate when present
                'manual_batches' => 'nullable|array',
                'manual_batches.*.quantity' => 'required_with:manual_batches|numeric|min:1',
                'manual_batches.*.expiration_date' => 'nullable|date|after_or_equal:today',
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
                'is_perishable' => 'sometimes|boolean',
                // Make batches nullable like manual method
                'batches' => 'nullable|array',
                'batches.*.quantity' => 'required_with:batches|numeric|min:1',
                'batches.*.expiration_date' => 'nullable|date|after_or_equal:today',
            ];
        }

        $validated = $request->validate($validationRules);

        // dd($request->all());

        // For manual method with batches, validate that total batches equal total stock
        if ($addMethod === 'manual' && isset($validated['manual_batches']) && !empty($validated['manual_batches'])) {
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

            // Handle perishable checkbox logic
            // Frontend sends "is_perishable" as 1 when "No Expiry" is checked.
            // Therefore: product is perishable when No Expiry is NOT checked.
            $isPerishable = !$request->boolean($fieldPrefix . 'is_perishable');

            // Prepare product data using foreign keys, not text fields
            $productData = [
                'productName' => $productName,
                'productSKU' => $newSKU,
                'brand_id' => $brand->id,           // ← Foreign key
                'category_id' => $category->id,     // ← Foreign key
                'productItemMeasurement' => $validated[$fieldPrefix . 'productItemMeasurement'],
                'productSellingPrice' => $validated[$fieldPrefix . 'productSellingPrice'],
                'productCostPrice' => $validated[$fieldPrefix . 'productCostPrice'],
                'is_perishable' => $isPerishable,
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

            // Create batches - handle both cases (with batches and without batches for non-perishable)
            $batchesField = $fieldPrefix . 'batches';
            $batchesToCreate = [];

            // Determine the total stock quantity based on the method
            if ($addMethod === 'po') {
                // For PO method, get stock from the selected PO item
                $poItem = PurchaseOrderItem::find($validated['selectedItemId']);
                $totalStockQuantity = $poItem ? $poItem->quantity : 0;
                
                // Adjust for bad items if any
                if (isset($validated['badItemQuantity']) && $validated['badItemQuantity'] > 0) {
                    $totalStockQuantity = max(0, $totalStockQuantity - $validated['badItemQuantity']);
                }
            } else {
                // For manual method, use the manual_productStock field
                $totalStockQuantity = $validated[$fieldPrefix . 'productStock'];
            }

            if (isset($validated[$batchesField]) && !empty($validated[$batchesField])) {
                // User provided batches
                foreach ($validated[$batchesField] as $batchIndex => $batch) {
                    // Use the new batch number format
                    $batchNumber = $this->generateBatchNumber($product->id);
                    
                    $batchesToCreate[] = [
                        'batch_number' => $batchNumber,
                        'quantity' => $batch['quantity'],
                        // FIX: Set expiration_date based on correct perishable logic
                        'expiration_date' => $isPerishable ? ($batch['expiration_date'] ?? null) : null,
                    ];
                }
            } else {
                // No batches provided
                if ($isPerishable) {
                    // Perishable items must have batches with expiration dates
                    DB::rollBack();
                    return back()->withInput()->withErrors([
                        'error' => 'Perishable items require batch entries with expiration dates.'
                    ]);
                }
                // Non-perishable: create a single batch with null expiration
                $batchNumber = $this->generateBatchNumber($product->id);
                $batchesToCreate[] = [
                    'batch_number' => $batchNumber,
                    'quantity' => $totalStockQuantity,
                    'expiration_date' => null,
                ];
            }

            // Create all batches
            foreach ($batchesToCreate as $batchData) {
                $fullBatchData = array_merge($batchData, [
                    'product_id' => $product->id,
                    'cost_price' => $validated[$fieldPrefix . 'productCostPrice'],
                    'selling_price' => $validated[$fieldPrefix . 'productSellingPrice'],
                ]);

                // Add PO references for PO method
                if ($addMethod === 'po') {
                    $fullBatchData['purchase_order_id'] = $validated['purchaseOrderNumber'];
                    $fullBatchData['purchase_order_item_id'] = $validated['selectedItemId'];
                }

                ProductBatch::create($fullBatchData);
            }

            // For PO method, handle bad items
            if ($addMethod === 'po' && isset($validated['productQuality']) && $validated['productQuality'] !== 'goodCondition') {
                $badItemCount = $validated['badItemQuantity'] ?? 0;
                if ($badItemCount > 0) {
                    BadItem::create([
                        'purchase_order_item_id' => $validated['selectedItemId'],
                        'purchase_order_id' => $validated['purchaseOrderNumber'],
                        'quality_status' => $validated['productQuality'],
                        'item_count' => $badItemCount,
                        'notes' => 'Added via inventory system',
                        'status' => 'Pending'
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

    // Add this method to your InventoryController
    private function generateBatchNumber($productId)
    {
        // Simplified per-product incremental format: B-001, B-002, ...
        $lastBatch = ProductBatch::where('product_id', $productId)
            ->where('batch_number', 'like', 'B-%')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastBatch && preg_match('/^B-(\d{3})$/', $lastBatch->batch_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            // Fallback: try to parse any B-<number> pattern
            if ($lastBatch && preg_match('/^B-(\d+)/', $lastBatch->batch_number, $m2)) {
                $nextNumber = intval($m2[1]) + 1;
            } else {
                $nextNumber = 1;
            }
        }

        return 'B-' . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
    }

}
