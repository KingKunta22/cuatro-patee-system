<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PurchaseOrderItem;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['category', 'brand']);
        
        // Apply time period filter
        $timePeriod = $request->timePeriod ?? 'all'; // Default to 'all'

        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'lastWeek':
                    $query->whereBetween('created_at', [now()->subDays(7), now()]);
                    break;
                case 'lastMonth':
                    $query->whereBetween('created_at', [now()->subDays(30), now()]);
                    break;
            }
        }

        $inventories = $query->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->withQueryString();

        // Debug: Check what's in the database
        $allPurchaseItems = PurchaseOrderItem::with('purchaseOrder.deliveries')->get();
        $deliveredPOs = PurchaseOrderItem::whereHas('purchaseOrder.deliveries', function($query) {
            $query->where('orderStatus', 'Delivered');
        })->get();
        
        // Debug output
        // \Log::info("All Purchase Items Count: " . $allPurchaseItems->count());
        // \Log::info("Delivered PO Items Count: " . $deliveredPOs->count());
        
        // Calculate total stock in (from purchase orders)
        $stockInQuery = PurchaseOrderItem::whereHas('purchaseOrder.deliveries', function($query) {
            $query->where('orderStatus', 'Delivered');
        });

        // Calculate total stock out (from sales)
        $stockOutQuery = SaleItem::query();


        // Apply time period filters if needed
        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $stockInQuery->whereDate('purchase_order_items.created_at', today());
                    $stockOutQuery->whereHas('sale', function($q) {
                        $q->whereDate('sale_date', today());
                    });
                    break;
                case 'lastWeek':
                    $stockInQuery->whereBetween('purchase_order_items.created_at', [now()->subDays(7), now()]);
                    $stockOutQuery->whereHas('sale', function($q) {
                        $q->whereBetween('sale_date', [now()->subDays(7), now()]);
                    });
                    break;
                case 'lastMonth':
                    $stockInQuery->whereBetween('purchase_order_items.created_at', [now()->subDays(30), now()]);
                    $stockOutQuery->whereHas('sale', function($q) {
                        $q->whereBetween('sale_date', [now()->subDays(30), now()]);
                    });
                    break;
            }
        }

        $totalStockIn = $stockInQuery->sum('quantity');
        $totalStockOut = $stockOutQuery->sum('quantity');
        
        // Debug output
        // \Log::info("Time Period: " . $timePeriod);
        // \Log::info("Total Stock In: " . $totalStockIn);
        // \Log::info("Total Stock Out: " . $totalStockOut);

        return view('reports.inventory-reports', compact(
            'inventories', 
            'totalStockIn', 
            'totalStockOut',
            'timePeriod'
        ));
    }
}