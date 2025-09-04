<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductMovementReportsController extends Controller
{
    public function index()
    {
        return view('reports.product-movement-reports');
    }
}