<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with([
            'category', 
            'brand', 
            'batches'
        ]);
        
        // Apply time period filter
        $timePeriod = $request->timePeriod ?? 'all';

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

        $products = $query->orderBy('created_at', 'DESC')
            ->paginate(10, ['*'], 'inventory_page')
            ->withQueryString();

        // Calculate total stock in (from product batches)
        $stockInQuery = ProductBatch::query();

        // Calculate total stock out (from sales)
        $stockOutQuery = SaleItem::query();

        // Apply time period filters
        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $stockInQuery->whereDate('created_at', today());
                    $stockOutQuery->whereDate('created_at', today());
                    break;
                case 'lastWeek':
                    $stockInQuery->whereBetween('created_at', [now()->subDays(7), now()]);
                    $stockOutQuery->whereBetween('created_at', [now()->subDays(7), now()]);
                    break;
                case 'lastMonth':
                    $stockInQuery->whereBetween('created_at', [now()->subDays(30), now()]);
                    $stockOutQuery->whereBetween('created_at', [now()->subDays(30), now()]);
                    break;
            }
        }

        $totalStockIn = $stockInQuery->sum('quantity');
        $totalStockOut = $stockOutQuery->sum('quantity');
        
        return view('reports.inventory-reports', compact(
            'products', // Changed from inventories
            'totalStockIn', 
            'totalStockOut',
            'timePeriod'
        ));
    }
}