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
        
        // FIXED: Total Cost (based on inventory additions)
        $totalCost = ProductBatch::when($dateRange, function($query) use ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->sum(DB::raw('cost_price * quantity')); // FIXED LINE
        
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
        $salesTrends = $this->getSalesTrendsData($salesPeriod);
        
        // Stock Level breakdown - Count PRODUCTS by their TOTAL stock
        $productsWithStock = Product::with(['batches'])->get();

        $inStock = $productsWithStock->filter(function($product) {
            $totalStock = $product->batches->sum('quantity');
            return $totalStock > 10;
        })->count();

        $lowStock = $productsWithStock->filter(function($product) {
            $totalStock = $product->batches->sum('quantity');
            return $totalStock >= 1 && $totalStock <= 10; // Low stock: 1-10
        })->count();

        $outOfStock = $productsWithStock->filter(function($product) {
            $totalStock = $product->batches->sum('quantity');
            return $totalStock == 0; // Out of stock: exactly 0
        })->count();
        
        // Low Stock Products - Include products with 0-10 stock (both low stock and out of stock)
        $lowStockProducts = Product::with(['batches'])
            ->get()
            ->filter(function($product) {
                $totalStock = $product->batches->sum('quantity');
                return $totalStock <= 10; // Changed from 1-10 to 0-10
            })
            ->sortBy(function($product) {
                return $product->batches->sum('quantity'); // Sort by stock level (lowest first)
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
        $labels = collect();
        $data = collect();

        if ($period === 'lastWeek') {
            // Past 7 days, label by day name
            $salesData = Sale::selectRaw('DATE(sale_date) as d, SUM(total_amount) as total')
                ->whereBetween('sale_date', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                ->groupBy('d')
                ->orderBy('d')
                ->get();

            $range = collect(range(0, 6))->map(fn($i) => now()->subDays(6 - $i)->startOfDay());
            $labels = $range->map(fn($dt) => $dt->format('D'));
            $data = $range->map(function($dt) use ($salesData) {
                $row = $salesData->firstWhere('d', $dt->toDateString());
                return (float) ($row->total ?? 0);
            });
        } elseif ($period === 'lastMonth') {
            // Past 4 full weeks (ISO week numbers)
            $start = now()->subWeeks(3)->startOfWeek();
            $end = now()->endOfWeek();
            $salesData = Sale::selectRaw('YEARWEEK(sale_date, 1) as yw, SUM(total_amount) as total')
                ->whereBetween('sale_date', [$start, $end])
                ->groupBy('yw')
                ->orderBy('yw')
                ->get();

            $weeks = collect(range(0, 3))->map(fn($i) => $start->copy()->addWeeks($i));
            $labels = $weeks->map(fn($dt) => 'Week ' . $dt->isoWeek);
            $data = $weeks->map(function($dt) use ($salesData) {
                $yw = (int) $dt->format('oW');
                $row = $salesData->firstWhere('yw', $yw);
                return (float) ($row->total ?? 0);
            });
        } elseif ($period === 'last6Months') {
            // Past 6 months by month name
            $start = now()->subMonths(5)->startOfMonth();
            $salesData = Sale::selectRaw('DATE_FORMAT(sale_date, "%Y-%m-01") as m, SUM(total_amount) as total')
                ->where('sale_date', '>=', $start)
                ->groupBy('m')
                ->orderBy('m')
                ->get();

            $months = collect(range(0, 5))->map(fn($i) => $start->copy()->addMonths($i));
            $labels = $months->map(fn($dt) => $dt->format('M Y'));
            $data = $months->map(function($dt) use ($salesData) {
                $mkey = $dt->format('Y-m-01');
                $row = $salesData->firstWhere('m', $mkey);
                return (float) ($row->total ?? 0);
            });
        }

        return response()->json([
            'labels' => $labels->values(),
            'data' => $data->values()
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
        // Return initial data to render page without AJAX
        $request = new Request(['period' => $period]);
        $response = $this->getSalesTrends($request);
        return $response->getData(true);
    }

}