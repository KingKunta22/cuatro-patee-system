<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['items', 'items.inventory']);
        
        // Apply search filter
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('invoice_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('customer_name', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply time period filter
        if ($request->timePeriod && $request->timePeriod !== 'all') {
            switch ($request->timePeriod) {
                case 'daily':
                    $query->whereDate('sale_date', today());
                    break;
                case 'weekly':
                    $query->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'monthly':
                    $query->whereMonth('sale_date', now()->month)
                          ->whereYear('sale_date', now()->year);
                    break;
            }
        }

        $sales = $query->orderBy('sale_date', 'DESC')
            ->paginate(10)
            ->withQueryString();

        // Calculate revenue stats (same as in SalesController)
        $totalRevenue = SaleItem::sum(DB::raw('quantity * unit_price'));
        $totalCost = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
            ->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        $totalProfit = $totalRevenue - $totalCost;

        return view('reports.sales-reports', compact('sales', 'totalRevenue', 'totalCost', 'totalProfit'));
    }
}