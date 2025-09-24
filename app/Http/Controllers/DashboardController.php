<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get time period filter - default to 'today'
        $timePeriod = $request->get('timePeriod', 'today');
        $salesPeriod = $request->get('salesPeriod', 'lastMonth'); // Add this line

        $dateRange = $this->getDateRange($timePeriod);
        
        // Total Sales (revenue)
        $totalSales = SaleItem::when($dateRange, function($query) use ($dateRange) {
                $query->whereHas('sale', function($q) use ($dateRange) {
                    $q->whereBetween('sale_date', [$dateRange['start'], $dateRange['end']]);
                });
            })
            ->sum(DB::raw('quantity * unit_price'));
        
        // In DashboardController::index() - FIXED
        // Total Cost (ONLY from delivered POs)
        $totalCost = SaleItem::when($dateRange, function($query) use ($dateRange) {
                $query->whereHas('sale', function($q) use ($dateRange) {
                    $q->whereBetween('sale_date', [$dateRange['start'], $dateRange['end']]);
                });
            })
            ->join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('purchase_orders', 'product_batches.purchase_order_id', '=', 'purchase_orders.id')
            ->join('deliveries', 'purchase_orders.id', '=', 'deliveries.purchase_order_id')
            ->where('deliveries.orderStatus', 'Delivered') // ← CRITICAL FIX
            ->sum(DB::raw('sale_items.quantity * product_batches.cost_price'));
        
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
        
        // Stock Level breakdown (FIXED) - Count PRODUCTS by their TOTAL stock
        $productsWithStock = Product::with(['batches'])->get();

        // Sales Trends Data (use the selected period)
        $salesTrends = $this->getSalesTrendsData($salesPeriod); // Change this line

        $inStock = $productsWithStock->filter(function($product) {
            $totalStock = $product->batches->sum('quantity');
            return $totalStock > 10;
        })->count();

        $lowStock = $productsWithStock->filter(function($product) {
            $totalStock = $product->batches->sum('quantity');
            return $totalStock >= 1 && $totalStock <= 10;
        })->count();

        $outOfStock = $productsWithStock->filter(function($product) {
            $totalStock = $product->batches->sum('quantity');
            return $totalStock == 0;
        })->count();
        
        // Low Stock Products (FIXED) - Only products with 1-10 total stock
        $lowStockProducts = Product::with(['batches'])
            ->get()
            ->filter(function($product) {
                $totalStock = $product->batches->sum('quantity');
                return $totalStock >= 1 && $totalStock <= 10;
            })
            ->take(5)
            ->map(function($product) {
                $totalStock = $product->batches->sum('quantity');
                return [
                    'productName' => $product->productName,
                    'productStock' => $totalStock
                ];
            });


        // Top Selling Products (last 30 days)
        $topSellingProducts = SaleItem::whereHas('sale', function($query) {
                $query->where('sale_date', '>=', now()->subDays(30));
            })
            ->with(['product' => function($query) {
                $query->select('id', 'productName', 'productImage', 'productSKU', 'productSellingPrice');
            }])
            ->select('product_id', 'product_name', 'unit_price', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id', 'product_name', 'unit_price')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Expiring Products (within next 30 days)
        $expiringProducts = ProductBatch::where('expiration_date', '>=', now())
            ->where('expiration_date', '<=', now()->addDays(30))
            ->with('product')
            ->orderBy('expiration_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function($batch) {
                return [
                    'productName' => $batch->product->productName,
                    'productExpirationDate' => $batch->expiration_date,
                    'batch_number' => $batch->batch_number,
                    'productSKU' => $batch->product->productSKU
                ];
            });
        
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
            'timePeriod',
            'salesTrends'
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

    // private function getSalesTrendsData($period = 'lastMonth')
    // {
    //     // Simple query - get monthly sales for the last 6 months
    //     $salesData = Sale::selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, SUM(total_amount) as total')
    //         ->where('sale_date', '>=', now()->subMonths(6))
    //         ->groupBy('year', 'month')
    //         ->orderBy('year')
    //         ->orderBy('month')
    //         ->get();

    //     $labels = [];
    //     $data = [];

    //     foreach ($salesData as $sale) {
    //         $date = Carbon::create($sale->year, $sale->month);
    //         $labels[] = $date->format('M Y');
    //         $data[] = (float) $sale->total;
    //     }

    //     // If no data, use default values
    //     if (empty($data)) {
    //         $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    //         $data = [0, 0, 0, 0, 0, 0];
    //     }

    //     return [
    //         'labels' => $labels,
    //         'data' => $data
    //     ];
    // }

    private function getSalesTrendsData($period = 'lastMonth')
    {
        // SUPER SIMPLE - Always return test data to make sure chart works
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [5000, 8000, 12000, 6000, 15000, 18000]
        ];
    }

}