<?php

// Allows UserController to be referenced from this Route
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductClassification;
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


Route::get('/reports', function() {
    return view('reports');
})->middleware('auth');


// This resource route doesn't route the user to the /suppliers. 
// This only allows us to use suppliers.store so that we can put it--
// inside the action attribute inside the form

// ROUTE FOR SALES
Route::resource('sales', SalesController::class)->middleware('auth');


// ROUTES FOR INVENTORY
Route::resource('inventory', InventoryController::class)->middleware('auth');
Route::get('/get-items/{poId}', [InventoryController::class, 'getItems'])->middleware('auth');


// ROUTE FOR PURCHASE ORDERS
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


// ROUTE FOR DELIVERY MANAGEMENT
Route::resource('delivery-management', DeliveryController::class)->middleware('auth');


// ROUTES FOR PRODUCTS CLASSIFICATION
Route::resource('product-classification', ProductClassification::class)->middleware('auth');

// Custom destroy routes for delete buttons
Route::delete('brands/{id}', [ProductClassification::class, 'destroyBrand'])->name('brands.destroy')->middleware('auth');
Route::delete('categories/{id}', [ProductClassification::class, 'destroyCategory'])->name('categories.destroy')->middleware('auth');
Route::delete('subcategories/{id}', [ProductClassification::class, 'destroySubcategory'])->name('subcategories.destroy')->middleware('auth');



// ROUTE FOR SUPPLIERS
Route::resource('suppliers', SupplierController::class)->middleware('auth');


// ROUTE FOR CUSTOMERS
Route::resource('customers', CustomerController::class)->middleware('auth');



/*

    ====MANUAL====
    array:12 [▼ // app\Http\Controllers\InventoryController.php:45
    "_token" => "t7mdbi4EwlnmeJCNLYUE9cena5gFqt5Tt8EBoba8"
    "productName" => null
        "productSKU" => "INV-202508-0011"
        "productBrand" => "Whiskas"
        "productCategory" => "Dog Toy"
    "productStock" => "0"
    "productSellingPrice" => "0"
    "productCostPrice" => "0"
        "productItemMeasurement" => "pcs"
    "productExpirationDate" => null
        "purchaseOrderNumber" => "108"
    "productProfitMargin" => "0%"
    ]
    ================================================================== productName, 
    ====PURCHASEORDER====
    array:14 [▼ // app\Http\Controllers\InventoryController.php:45
    "_token" => "t7mdbi4EwlnmeJCNLYUE9cena5gFqt5Tt8EBoba8"
    "productName" => "ManualPurchaseOrder2"
    "productSKU" => "INV-202508-0011"
    "productStock" => "5"
    "productSellingPrice" => "300"
    "productCostPrice" => "200.00"
    "productExpirationDate" => "2025-09-06"
    "purchaseOrderNumber" => "118"
    "selectedItemId" => "91"
    "productBrand" => "Whiskas"
    "productCategory" => "Dog Toy"
    "productProfitMargin" => "50.00%"
    "productItemMeasurement" => "liter"
    "productImage" => 
    Illuminate\Http
    \
    UploadedFile
    {#1291 ▶}
    ]

*/