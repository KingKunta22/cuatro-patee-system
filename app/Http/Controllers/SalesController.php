<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::where('customerStatus', 'Active')
                    ->select('id', 'customerName')
                    ->get();

        return view('/sales', compact('customers'));
    }
}
