<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PONotesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductClassification;
use App\Http\Controllers\SalesReportsController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\InventoryReportsController;
use App\Http\Controllers\PurchaseOrderReportsController;
use App\Http\Controllers\ProductMovementReportsController;
use App\Http\Controllers\ForgotPasswordController;

// Authentication routes
Route::get('/', function() {
    return view('login');
})->name('login');

Route::post('/login', [UserController::class, 'login']);
Route::post('/forgot-password/send-code', [ForgotPasswordController::class, 'sendCode'])->name('forgot.send');
Route::post('/forgot-password/validate-code', [ForgotPasswordController::class, 'validateCode'])->name('forgot.validate');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('forgot.reset');
Route::post('/logout', [UserController::class, 'logout']);

// All routes that require authentication
Route::middleware('auth')->group(function () {
    
    // === ROUTES ACCESSIBLE BY BOTH STAFF & ADMIN ===
    Route::get('/main', [DashboardController::class, 'index'])->name('main');
    Route::resource('sales', SalesController::class);
    Route::get('/sales/{sale}/download-receipt', [SalesController::class, 'downloadSaleReceipt'])->name('sales.download-receipt');
    Route::get('/sales/{id}/edit', [SalesController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{id}', [SalesController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{id}', [SalesController::class, 'destroy'])->name('sales.destroy');
    Route::resource('inventory', InventoryController::class);
    Route::get('/get-items/{poId}', [InventoryController::class, 'getItems']);
    Route::resource('purchase-orders', PurchaseOrderController::class)->except(['update']);
    Route::post('purchase-orders/add-item', [PurchaseOrderController::class, 'addItem'])->name('purchase-orders.add-item');
    Route::delete('purchase-orders/remove-item/{index}', [PurchaseOrderController::class, 'removeItem'])->name('purchase-orders.remove-item');
    Route::post('purchase-orders/clear-session', [PurchaseOrderController::class, 'clearSession'])->name('purchase-orders.clearSession');
    Route::put('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
    Route::delete('/purchase-orders/{purchaseOrder}/items/{item}', [App\Http\Controllers\PurchaseOrderController::class, 'destroyItem'])->name('purchase-orders.items.destroy');
    Route::get('/download-pdf/{orderId}', [PurchaseOrderController::class, 'downloadPDF'])->name('purchase-orders.download-pdf');
    Route::resource('delivery-management', DeliveryController::class);
    Route::post('/delivery-management/update-status', [DeliveryController::class, 'updateStatus'])->name('delivery-management.updateStatus');
    Route::resource('product-classification', ProductClassification::class);
    Route::delete('brands/{id}', [ProductClassification::class, 'destroyBrand'])->name('brands.destroy');
    Route::delete('categories/{id}', [ProductClassification::class, 'destroyCategory'])->name('categories.destroy');
    Route::delete('subcategories/{id}', [ProductClassification::class, 'destroySubcategory'])->name('subcategories.destroy');
    Route::resource('po-notes', PONotesController::class);
    Route::get('/dashboard/sales-trends', [DashboardController::class, 'getSalesTrends'])->name('dashboard.sales-trends');

    // === SEMI STAFF ROUTES (features are hidden from staff access)
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    
    // === ADMIN-ONLY ROUTES (protected by controller checks) ===
    // REMOVE THE MIDDLEWARE GROUP - just list the routes directly
    Route::get('/manage-account', [UserController::class, 'index'])->name('manage.account');
    Route::post('/manage-account', [UserController::class, 'store'])->name('users.store');
    Route::put('/manage-account/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/manage-account/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::resource('suppliers', SupplierController::class);
});

Route::get('/check-notifications', function () {
    $notifications = [];
    
    // 1. Check low stock (stock <= 10)
    $lowStockProducts = \App\Models\Product::with('batches')->get();
    
    foreach ($lowStockProducts as $product) {
        $totalStock = $product->batches->sum('quantity');
        if ($totalStock <= 10) {
            $notifications[] = [
                'id' => 'low_stock_' . $product->id,
                'title' => 'Low Stock Alert',
                'message' => $product->productName . ' is running low! Stock: ' . $totalStock,
                'url' => '/inventory',
                'time' => 'Today',
                'created_at' => now()->timestamp // For sorting
            ];
        }
    }
    
    // 2. Check expiring products (within 30 days) - FIXED: No decimals
    $expiringBatches = \App\Models\ProductBatch::where('expiration_date', '<=', now()->addDays(30))
        ->where('expiration_date', '>', now())
        ->where('quantity', '>', 0)
        ->with('product')
        ->get();
    
    foreach ($expiringBatches as $batch) {
        $days = floor(now()->diffInDays($batch->expiration_date)); // FIXED: No decimals
        $notifications[] = [
            'id' => 'expiring_' . $batch->id,
            'title' => 'Product Expiring Soon',
            'message' => $batch->product->productName . " expires in {$days} days",
            'url' => '/inventory',
            'time' => 'Today',
            'created_at' => now()->timestamp
        ];
    }
    
    // 3. Check for delivered orders that aren't in inventory - IMPROVED
    $deliveredOrders = \App\Models\PurchaseOrder::whereHas('deliveries', function($query) {
        $query->where('orderStatus', 'Delivered');
    })->with(['items', 'deliveries'])->get();
    
    foreach ($deliveredOrders as $order) {
        $hasUnaddedItems = false;
        
        foreach ($order->items as $item) {
            $batchExists = \App\Models\ProductBatch::where('purchase_order_id', $order->id)
                ->where('purchase_order_item_id', $item->id)
                ->exists();
                
            if (!$batchExists) {
                $hasUnaddedItems = true;
                break;
            }
        }
        
        if ($hasUnaddedItems) {
            $notifications[] = [
                'id' => 'delivered_' . $order->id,
                'title' => 'Delivery Ready for Inventory',
                'message' => "Order #{$order->id} has been delivered - add items to inventory",
                'url' => '/inventory?add_delivery=' . $order->id,
                'time' => 'Today',
                'created_at' => $order->deliveries->first()->status_updated_at?->timestamp ?? now()->timestamp
            ];
        }
    }
    
    // 4. ORDER BY NEWEST TO LATEST (most recent first)
    usort($notifications, function($a, $b) {
        return ($b['created_at'] ?? 0) <=> ($a['created_at'] ?? 0);
    });
    
    return response()->json(['notifications' => $notifications]);
});

// Route::get('/test-costs', function() {
//     // Test delivered purchase orders cost
//     $deliveredCost = \App\Models\SaleItem::join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
//         ->join('purchase_orders', 'product_batches.purchase_order_id', '=', 'purchase_orders.id')
//         ->join('deliveries', 'purchase_orders.id', '=', 'deliveries.purchase_order_id')
//         ->where('deliveries.orderStatus', 'Delivered')
//         ->sum(\DB::raw('sale_items.quantity * product_batches.cost_price'));

//     // Test all purchase orders cost (should be higher)
//     $allCost = \App\Models\SaleItem::join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
//         ->sum(\DB::raw('sale_items.quantity * product_batches.cost_price'));

//     return [
//         'delivered_cost' => $deliveredCost,
//         'all_cost' => $allCost,
//         'difference' => $allCost - $deliveredCost
//     ];
// });