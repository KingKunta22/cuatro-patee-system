<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Customer;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::where('customerStatus', 'Active')
                    ->select('id', 'customerName')
                    ->get();

        // Get inventories for the datalist
        $inventories = Inventory::where('productStock', '>', 0)
                       ->get(['id', 'productName', 'productSKU', 
                              'productBrand', 'productItemMeasurement', 
                              'productSellingPrice', 'productStock']);

        return view('/sales', compact('customers', 'inventories')); // Add inventories to compact
    }

    public function create()
    {
        // Use Inventory to get available stock with correct field names
        $inventories = Inventory::where('productStock', '>', 0) // Only items with stock
                       ->get(['id', 'productName', 'productSKU', 
                              'productBrand', 'productItemMeasurement', 
                              'productSellingPrice', 'productStock']);
        
        $customers = Customer::all();
        
        return view('sales.create', compact('inventories', 'customers'));
    }


    public function store(Request $request)
    {
        return redirect()->route('sales.index')->with('success', 'Sale added successfully!');
    }

}