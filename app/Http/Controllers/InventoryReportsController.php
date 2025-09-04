<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryReportsController extends Controller
{
    public function index()
    {
        $inventories = Inventory::with(['category', 'brand'])
                        ->orderBy('created_at', 'DESC')
                        ->paginate(10);

        return view('reports.inventory-reports', compact('inventories'));
    }

    // You can add more methods specific to inventory reports here later
    // e.g., public function export(), public function search(Request $request), etc.
}