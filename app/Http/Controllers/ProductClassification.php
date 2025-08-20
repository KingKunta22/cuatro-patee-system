<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductClassification extends Controller
{
    public function index(Request $request)
    {
        return view('product-classification');
    }
}
