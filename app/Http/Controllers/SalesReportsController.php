<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportsController extends Controller
{
    public function index(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        $salesQuery = Sale::with(['items']);
        
        // Apply time period filter if needed
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
        
        // sales_page parameter instead of page
        $sales = $salesQuery->orderBy('sale_date', 'DESC')
            ->paginate(10, ['*'], 'sales_page');
        
        // Calculate stats
        $revenueQuery = Sale::query();
        $costQuery = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id');
        
        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $revenueQuery->whereDate('sale_date', today());
                    $costQuery->whereHas('sale', function($q) {
                        $q->whereDate('sale_date', today());
                    });
                    break;
                case 'lastWeek':
                    $revenueQuery->whereBetween('sale_date', [now()->subDays(7), now()]);
                    $costQuery->whereHas('sale', function($q) {
                        $q->whereBetween('sale_date', [now()->subDays(7), now()]);
                    });
                    break;
                case 'lastMonth':
                    $revenueQuery->whereBetween('sale_date', [now()->subDays(30), now()]);
                    $costQuery->whereHas('sale', function($q) {
                        $q->whereBetween('sale_date', [now()->subDays(30), now()]);
                    });
                    break;
            }
        }
        
        $totalRevenue = $revenueQuery->sum('total_amount');
        $totalCost = $costQuery->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        $totalProfit = $totalRevenue - $totalCost;
        
        return view('reports.sales-reports', compact(
            'sales', 
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'timePeriod'
        ));
    }
}