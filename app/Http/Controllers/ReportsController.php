<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SaleItem;
use App\Models\Sale; // Add this import
use Illuminate\Support\Facades\DB; // Add this import
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

        // Data for Sales tab
        $sales = Sale::with(['items', 'items.inventory'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        // Calculate stock totals for inventory reports
        $totalStockIn = PurchaseOrderItem::whereHas('purchaseOrder.deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->sum('quantity');

        $totalStockOut = SaleItem::sum('quantity');

        // Calculate revenue stats for sales reports (same as in SalesController)
        $totalRevenue = SaleItem::sum(DB::raw('quantity * unit_price'));
        $totalCost = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
            ->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        $totalProfit = $totalRevenue - $totalCost;

        return view('reports', compact('inventories', 'purchaseOrders', 'sales', 'totalStockIn', 'totalStockOut', 'totalRevenue', 'totalCost', 'totalProfit'));
    }
}