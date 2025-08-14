<?php

// Allows UserController to be referenced from this Route
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;

Route::get('/', function() {
    return view('login');
})->name('login');

Route::post('/login', [UserController::class, 'login']);

// The middleware method doesn't allow unlogged users to open this URL
Route::get('/main', function() {
    return view('main');
})->middleware('auth');

// The function is inside the UserController.php for cleaner code
Route::post('/logout', [UserController::class, 'logout']);

Route::get('/sales', function() {
    return view('sales');
})->middleware('auth');

Route::get('/inventory', function() {
    return view('inventory');
})->middleware('auth');

Route::get('/purchase-order', function() {
    return view('purchase-order');
})->middleware('auth');

Route::get('/reports', function() {
    return view('reports');
})->middleware('auth');

Route::get('/product-classification', function() {
    return view('product-classification');
})->middleware('auth');

Route::get('/delivery-management', function() {
    return view('delivery-management');
})->middleware('auth');

// This resource route doesn't route the user to the /suppliers. 
// This only allows us to use suppliers.store so that we can put it--
// inside the action attribute inside the form
Route::resource('suppliers', SupplierController::class)->middleware('auth');
Route::resource('customers', CustomerController::class)->middleware('auth');

// Resource route for purchase orders
Route::resource('purchase-orders', PurchaseOrderController::class)->except(['update'])->middleware('auth');

// ============== Custom routes for purchase order session management ==========

// For adding items to the session
Route::post('purchase-orders/add-item', [PurchaseOrderController::class, 'addItem'])->name('purchase-orders.add-item')->middleware('auth');
// For removing items from the session
Route::delete('purchase-orders/remove-item/{index}', [PurchaseOrderController::class, 'removeItem'])->name('purchase-orders.remove-item')->middleware('auth');
// For clearing the session
Route::post('purchase-orders/clear-session', [PurchaseOrderController::class, 'clearSession'])->name('purchase-orders.clearSession')->middleware('auth');
// To update session and the existing values
Route::put('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update')->middleware('auth');
// To delete existing items
Route::delete('/purchase-orders/{purchaseOrder}/items/{item}', [App\Http\Controllers\PurchaseOrderController::class, 'destroyItem'])->name('purchase-orders.items.destroy');


// FOR PDF
Route::get('/download-pdf/{orderId}', [PurchaseOrderController::class, 'downloadPDF'])->name('purchase-orders.download-pdf');