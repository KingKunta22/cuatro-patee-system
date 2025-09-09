<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
        // Data for inventory tab
        $inventories = Inventory::with(['category', 'brand'])
                        ->orderBy('created_at', 'DESC')
                        ->paginate(10);

        // Data for PO tab
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items', 'items.inventory', 'items.badItems', 'notes'])
            ->whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);


        return view('reports', compact('inventories', 'purchaseOrders'));
    }
}