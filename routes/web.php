<?php

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

// Authentication routes
Route::get('/', function() {
    return view('login');
})->name('login');

Route::post('/login', [UserController::class, 'login']);
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
    
    // === ADMIN-ONLY ROUTES (protected by controller checks) ===
    // REMOVE THE MIDDLEWARE GROUP - just list the routes directly
    Route::get('/manage-account', [UserController::class, 'index'])->name('manage.account');
    Route::post('/manage-account', [UserController::class, 'store'])->name('users.store');
    Route::put('/manage-account/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/manage-account/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::resource('suppliers', SupplierController::class);
});