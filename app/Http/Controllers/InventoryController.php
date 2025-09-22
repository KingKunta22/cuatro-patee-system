<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BadItem;
use App\Models\Category;
use App\Models\SaleItem;
use App\Models\Inventory;
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

        // Current implementation shows all batches
        $inventoryItems = $query->orderBy('productSKU')
            ->orderBy('productExpirationDate', 'ASC') // Show oldest first
            ->paginate(7)
            ->withQueryString();

        // Get categories and brands from their models for dropdowns
        $categories = Category::orderBy('productCategory')->get();
        $brands = Brand::orderBy('productBrand')->get();

        // Get unique values from inventory for filters
        $uniqueCategories = Inventory::distinct()->pluck('productCategory')->filter();
        $uniqueBrands = Inventory::distinct()->pluck('productBrand')->filter();

        return view('inventory', compact(
            'unaddedPOs', 
            'inventoryItems', 
            'deliveredPOs', 
            'categories', 
            'brands', 
            'uniqueCategories', 
            'uniqueBrands',
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
                'manual_productStock' => 'required|numeric|min:0',
                'manual_productSellingPrice' => 'required|numeric|min:0',
                'manual_productCostPrice' => 'required|numeric|min:0',
                'manual_productItemMeasurement' => 'required|string|max:255',
                'manual_productExpirationDate' => 'required|date|after_or_equal:today',
                'manual_productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'manual_batches' => 'sometimes|array',
                'manual_batches.*.quantity' => 'required|numeric|min:1',
                'manual_batches.*.expiration_date' => 'required|date|after_or_equal:today',
            ];
        } else {
            $fieldPrefix = '';
            $validationRules = [
                'productName' => 'required|string|max:255',
                'productBrand' => 'required|string|max:255',
                'productCategory' => 'required|string|max:255',
                'productSellingPrice' => 'required|numeric|min:0',
                'productCostPrice' => 'required|numeric|min:0',
                'productItemMeasurement' => 'required|string|max:255',
                'productExpirationDate' => 'required|date|after_or_equal:today',
                'productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'purchaseOrderNumber' => 'required|exists:purchase_orders,id',
                'selectedItemId' => 'required|exists:purchase_order_items,id',
                'productQuality' => 'required|string',
                'badItemQuantity' => 'nullable|integer|min:0',
            ];
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
        if ($addMethod === 'po') {
            $poItem = PurchaseOrderItem::find($validated['selectedItemId']);
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
            // Use the first batch's expiration date as the main expiration date
            $validated[$fieldPrefix . 'productExpirationDate'] = $validated['manual_batches'][0]['expiration_date'];
        }

        
        $newSKU = $this->generateSKU($productName, $productBrand, $productCategory);
        
        // Generate batch number (YYYYMMDD-XXX format)
        $batchNumber = date('Ymd') . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Check if this exact batch already exists (same SKU + same expiration)
        $existingBatch = Inventory::where('productSKU', $newSKU)
            ->where('productExpirationDate', $validated[$fieldPrefix . 'productExpirationDate'])
            ->first();
        
        if ($existingBatch) {
            // If batch exists, update stock
            $stockToAdd = ($addMethod === 'po') ? $actualStock : $validated[$fieldPrefix . 'productStock'];
            $existingBatch->update([
                'productStock' => $existingBatch->productStock + $stockToAdd
            ]);
            
            return redirect()->route('inventory.index')->with('success', 'Product stock updated successfully!');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // For PO method, calculate actual stock (total - bad items)
            if ($addMethod === 'po') {
                $poItem = PurchaseOrderItem::find($validated['selectedItemId']);
                $totalQuantity = $poItem->quantity;
                $badItemCount = ($validated['productQuality'] !== 'goodCondition') 
                    ? ($validated['badItemQuantity'] ?? 0) 
                    : 0;
                
                $actualStock = $totalQuantity - $badItemCount;
                
                if ($actualStock < 0) {
                    throw new \Exception('Bad items cannot exceed total quantity');
                }
                
                if ($actualStock === 0) {
                    throw new \Exception('Cannot add product with zero stock');
                }
            }

            // Map fields to database fields
            $inventoryData = [
                'productName' => $productName,
                'productSKU' => $newSKU,
                'productBatch' => $batchNumber,
                'productBrand' => $productBrand,
                'productCategory' => $productCategory,
                'productStock' => ($addMethod === 'po') ? $actualStock : $validated[$fieldPrefix . 'productStock'],
                'productSellingPrice' => $validated[$fieldPrefix . 'productSellingPrice'],
                'productCostPrice' => $validated[$fieldPrefix . 'productCostPrice'],
                'productItemMeasurement' => $validated[$fieldPrefix . 'productItemMeasurement'],
                'productExpirationDate' => $validated[$fieldPrefix . 'productExpirationDate'],
                'purchase_order_id' => ($addMethod === 'po') ? $validated['purchaseOrderNumber'] : null,
                'purchase_order_item_id' => ($addMethod === 'po') ? $validated['selectedItemId'] : null,
            ];

            // Handle image upload
            $imageField = $fieldPrefix . 'productImage';
            if ($request->hasFile($imageField)) {
                $imagePath = $request->file($imageField)->store('inventory', 'public');
                $inventoryData['productImage'] = $imagePath;
            }

            // Calculate profit margin
            $profitMargin = $inventoryData['productSellingPrice'] - $inventoryData['productCostPrice'];
            $inventoryData['productProfitMargin'] = round($profitMargin, 2);

            // Save to database - handle multiple batches for manual method
            if ($addMethod === 'manual' && isset($validated['manual_batches'])) {
                $createdInventories = [];
                
                foreach ($validated['manual_batches'] as $batchIndex => $batch) {
                    $batchNumber = date('Ymd') . '-' . str_pad(($batchIndex + 1), 3, '0', STR_PAD_LEFT);
                    
                    $inventoryData = [
                        'productName' => $productName,
                        'productSKU' => $newSKU,
                        'productBatch' => $batchNumber,
                        'productBrand' => $productBrand,
                        'productCategory' => $productCategory,
                        'productStock' => $batch['quantity'],
                        'productSellingPrice' => $validated[$fieldPrefix . 'productSellingPrice'],
                        'productCostPrice' => $validated[$fieldPrefix . 'productCostPrice'],
                        'productItemMeasurement' => $validated[$fieldPrefix . 'productItemMeasurement'],
                        'productExpirationDate' => $batch['expiration_date'],
                        'productProfitMargin' => round($profitMargin, 2),
                    ];

                    // Handle image upload (only for first batch)
                    if ($batchIndex === 0 && $request->hasFile($imageField)) {
                        $imagePath = $request->file($imageField)->store('inventory', 'public');
                        $inventoryData['productImage'] = $imagePath;
                    } elseif ($batchIndex > 0 && isset($createdInventories[0])) {
                        // Copy image from first batch for subsequent batches
                        $inventoryData['productImage'] = $createdInventories[0]->productImage;
                    }

                    $inventory = Inventory::create($inventoryData);
                    $createdInventories[] = $inventory;
                }
            } else {
                // Original single inventory creation for PO method
                $inventory = Inventory::create($inventoryData);
                
                // Handle bad items if adding from PO and quality is not good
                if ($addMethod === 'po' && $validated['productQuality'] !== 'goodCondition' && $badItemCount > 0) {
                    BadItem::create([
                        'inventory_id' => $inventory->id,
                        'purchase_order_id' => $validated['purchaseOrderNumber'],
                        'purchase_order_item_id' => $validated['selectedItemId'],
                        'quality_status' => $validated['productQuality'],
                        'item_count' => $badItemCount,
                        'notes' => 'Reported during inventory addition from PO',
                        'status' => 'Pending',
                    ]);
                }
            }

            // Handle bad items if adding from PO and quality is not good
            if ($addMethod === 'po' && $validated['productQuality'] !== 'goodCondition' && $badItemCount > 0) {
                BadItem::create([
                    'inventory_id' => $inventory->id,
                    'purchase_order_id' => $validated['purchaseOrderNumber'],
                    'purchase_order_item_id' => $validated['selectedItemId'],
                    'quality_status' => $validated['productQuality'],
                    'item_count' => $badItemCount,
                    'notes' => 'Reported during inventory addition from PO',
                    'status' => 'Pending',
                ]);
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('inventory.index')->with('success', 'Product added successfully!');

        } catch (\Exception $e) {
            // Rollback the transaction on error
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
