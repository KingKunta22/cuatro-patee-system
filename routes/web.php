<?php

// Allows UserController to be referenced from this Route
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;

Route::get('/', function() {
    return view('login');
});

Route::post('/login', [UserController::class, 'login']);

// The middleware method doesn't allow unlogged users to open this URL
Route::get('/main', function() {
    return view('main');
})->middleware('auth');

// The function is inside the UserController.php for cleaner code
Route::post('/logout', [UserController::class, 'logout']);

Route::get('/sales', function() {
    return view('sales');
});

Route::get('/inventory', function() {
    return view('inventory');
});

Route::get('/purchase-order', function() {
    return view('purchase-order');
});

Route::get('/reports', function() {
    return view('reports');
});

Route::get('/product-classification', function() {
    return view('product-classification');
});

Route::get('/suppliers', function() {
    return view('suppliers');
});

Route::get('/manage-account', function() {
    return view('manage-account');
});

// This resource route doesn't route the user to the /suppliers. 
// This only allows us to use suppliers.store so that we can put it--
// inside the action attribute inside the form
Route::resource('suppliers', SupplierController::class);