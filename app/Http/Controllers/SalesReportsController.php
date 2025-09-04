<?php
// SalesReportsController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesReportsController extends Controller
{
    public function index()
    {
        // Your future sales report logic will go here
        return view('reports.sales-reports');
    }
}