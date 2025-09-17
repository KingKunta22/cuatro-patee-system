<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get time period filter - default to 'today'
        $timePeriod = $request->get('timePeriod', 'today');
        
        // Calculate date range based on time period
        $dateRange = $this->getDateRange($timePeriod);
        
        // Total Sales (revenue)
        $totalSales = SaleItem::when($dateRange, function($query) use ($dateRange) {
                $query->whereHas('sale', function($q) use ($dateRange) {
                    $q->whereBetween('sale_date', [$dateRange['start'], $dateRange['end']]);
                });
            })
            ->sum(DB::raw('quantity * unit_price'));
        
        // Total Cost
        $totalCost = SaleItem::when($dateRange, function($query) use ($dateRange) {
                $query->whereHas('sale', function($q) use ($dateRange) {
                    $q->whereBetween('sale_date', [$dateRange['start'], $dateRange['end']]);
                });
            })
            ->join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
            ->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        
        // Products Sold (count of items sold)
        $productsSold = SaleItem::when($dateRange, function($query) use ($dateRange) {
                $query->whereHas('sale', function($q) use ($dateRange) {
                    $q->whereBetween('sale_date', [$dateRange['start'], $dateRange['end']]);
                });
            })
            ->sum('quantity');
        
        // Total Transactions (count of sales)
        $totalTransactions = Sale::when($dateRange, function($query) use ($dateRange) {
                if ($dateRange) {
                    $query->whereBetween('sale_date', [$dateRange['start'], $dateRange['end']]);
                }
            })
            ->count();
        
        // Stock Level breakdown
        $inStock = Inventory::where('productStock', '>', 10)->count();
        $lowStock = Inventory::whereBetween('productStock', [1, 10])->count();
        $outOfStock = Inventory::where('productStock', 0)->count();
        
        // Low Stock Products (stock ≤ 10)
        $lowStockProducts = Inventory::whereBetween('productStock', [1, 10])
            ->orderBy('productStock', 'asc')
            ->limit(5)
            ->get(['productName', 'productStock']);
        
        // Top Selling Products (last 30 days)
        $topSellingProducts = SaleItem::whereHas('sale', function($query) {
                $query->where('sale_date', '>=', now()->subDays(30));
            })
            ->with(['inventory' => function($query) {
                $query->select('id', 'productSellingPrice', 'productSKU', 'productImage');
            }])
            ->select('product_name', 'inventory_id', 'unit_price', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_name', 'inventory_id', 'unit_price')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Expiring Products (within next 30 days)
        $expiringProducts = Inventory::where('productExpirationDate', '>=', now())
            ->where('productExpirationDate', '<=', now()->addDays(30))
            ->orderBy('productExpirationDate', 'asc')
            ->limit(5)
            ->get(['productName', 'productExpirationDate', 'productSKU']);
        
        return view('main', compact(
            'totalSales',
            'totalCost',
            'productsSold',
            'totalTransactions',
            'inStock',
            'lowStock',
            'outOfStock',
            'lowStockProducts',
            'topSellingProducts',
            'expiringProducts',
            'timePeriod'
        ));
    }
    
    private function getDateRange($timePeriod)
    {
        switch ($timePeriod) {
            case 'today':
                return [
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            case 'lastWeek':
                return [
                    'start' => now()->subWeek()->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            case 'lastMonth':
                return [
                    'start' => now()->subMonth()->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            case 'lastYear':
                return [
                    'start' => now()->subYear()->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            default: // 'all'
                return null;
        }
    }

    public function getSalesTrends(Request $request)
    {
        $period = $request->get('period', 'lastMonth');
        
        switch ($period) {
            case 'lastWeek':
                // Get sales data for the last week
                $salesData = Sale::selectRaw('DAYNAME(sale_date) as day, SUM(total_amount) as total')
                    ->whereBetween('sale_date', [now()->subWeek(), now()])
                    ->groupBy('day')
                    ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                    ->get();
                
                $labels = $salesData->pluck('day');
                $data = $salesData->pluck('total');
                break;
                
            case 'lastMonth':
                // Get sales data for the last month by weeks
                $salesData = Sale::selectRaw('WEEK(sale_date, 1) as week, SUM(total_amount) as total')
                    ->whereBetween('sale_date', [now()->subMonth(), now()])
                    ->groupBy('week')
                    ->orderBy('week')
                    ->get();
                
                $labels = $salesData->map(function($item) {
                    return 'Week ' . $item->week;
                });
                
                $data = $salesData->pluck('total');
                break;
                
            case 'last6Months':
                // Get sales data for the last 6 months
                $salesData = Sale::selectRaw('MONTHNAME(sale_date) as month, YEAR(sale_date) as year, SUM(total_amount) as total')
                    ->whereBetween('sale_date', [now()->subMonths(6), now()])
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderByRaw('MONTH(STR_TO_DATE(month, "%M"))')
                    ->get();
                
                $labels = $salesData->map(function($item) {
                    return $item->month . ' ' . $item->year;
                });
                
                $data = $salesData->pluck('total');
                break;
                
            default:
                $labels = [];
                $data = [];
        }
        
        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }


}