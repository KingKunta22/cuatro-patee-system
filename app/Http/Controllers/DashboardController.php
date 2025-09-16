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
        // Get time period filter
        $timePeriod = $request->get('timePeriod', 'all');
        
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
            ->with('inventory') // Eager load inventory relationship
            ->select('product_name', 'inventory_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_name', 'inventory_id')
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
                    'start' => now()->subDays(7)->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            case 'lastMonth':
                return [
                    'start' => now()->subDays(30)->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            default: // 'all'
                return null;
        }
    }
}