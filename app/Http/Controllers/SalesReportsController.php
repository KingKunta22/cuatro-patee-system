<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportsController extends Controller
{
    public function index(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        $salesQuery = Sale::with(['items', 'items.productBatch.product']);
        
        // Apply time period filter
        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $salesQuery->whereDate('sale_date', today());
                    break;
                case 'lastWeek':
                    $salesQuery->whereBetween('sale_date', [now()->subDays(7), now()]);
                    break;
                case 'lastMonth':
                    $salesQuery->whereBetween('sale_date', [now()->subDays(30), now()]);
                    break;
            }
        }
        
        $sales = $salesQuery->orderBy('sale_date', 'DESC')
            ->paginate(10, ['*'], 'sales_page');
        
        // Calculate stats
        $revenueQuery = Sale::query();
        
        // FIXED: Calculate costs based on inventory additions
        $costQuery = ProductBatch::query();

        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $costQuery->whereDate('created_at', today());
                    break;
                case 'lastWeek':
                    $costQuery->whereBetween('created_at', [now()->subDays(7), now()]);
                    break;
                case 'lastMonth':
                    $costQuery->whereBetween('created_at', [now()->subDays(30), now()]);
                    break;
            }
        }

        $totalCost = $costQuery->sum(DB::raw('cost_price * quantity')); // FIXED

        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $revenueQuery->whereDate('sale_date', today());
                    $costQuery->whereDate('sale_items.created_at', today());
                    break;
                case 'lastWeek':
                    $revenueQuery->whereBetween('sale_date', [now()->subDays(7), now()]);
                    $costQuery->whereBetween('sale_items.created_at', [now()->subDays(7), now()]);
                    break;
                case 'lastMonth':
                    $revenueQuery->whereBetween('sale_date', [now()->subDays(30), now()]);
                    $costQuery->whereBetween('sale_items.created_at', [now()->subDays(30), now()]);
                    break;
            }
        }

        $totalCost = $costQuery->sum(DB::raw('sale_items.quantity * product_batches.cost_price'));
        $totalRevenue = $revenueQuery->sum('total_amount');
        $totalProfit = $totalRevenue - $totalCost;
        
        return view('reports.sales-reports', compact(
            'sales', 
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'timePeriod'
        ));
    }

    public function print(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        $salesQuery = Sale::with(['items', 'items.productBatch.product', 'user']);
        
        // Apply time period filter
        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $salesQuery->whereDate('sale_date', today());
                    break;
                case 'lastWeek':
                    $salesQuery->whereBetween('sale_date', [now()->subDays(7), now()]);
                    break;
                case 'lastMonth':
                    $salesQuery->whereBetween('sale_date', [now()->subDays(30), now()]);
                    break;
            }
        }
        
        $sales = $salesQuery->orderBy('sale_date', 'DESC')->get();
        
        // Calculate stats
        $revenueQuery = Sale::query();
        $costQuery = SaleItem::join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('purchase_orders', 'product_batches.purchase_order_id', '=', 'purchase_orders.id')
            ->join('deliveries', 'purchase_orders.id', '=', 'deliveries.purchase_order_id')
            ->where('deliveries.orderStatus', 'Delivered');

        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $revenueQuery->whereDate('sale_date', today());
                    $costQuery->whereDate('sale_items.created_at', today());
                    break;
                case 'lastWeek':
                    $revenueQuery->whereBetween('sale_date', [now()->subDays(7), now()]);
                    $costQuery->whereBetween('sale_items.created_at', [now()->subDays(7), now()]);
                    break;
                case 'lastMonth':
                    $revenueQuery->whereBetween('sale_date', [now()->subDays(30), now()]);
                    $costQuery->whereBetween('sale_items.created_at', [now()->subDays(30), now()]);
                    break;
            }
        }

        $totalCost = $costQuery->sum(DB::raw('sale_items.quantity * product_batches.cost_price'));
        $totalRevenue = $revenueQuery->sum('total_amount');
        $totalProfit = $totalRevenue - $totalCost;
        
        return view('reports.print.sales-print', compact(
            'sales', 
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'timePeriod'
        ));
    }
}