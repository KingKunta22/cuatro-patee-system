<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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
        
        return view('reports.inventory-reports', compact(
            'products',
            'timePeriod'
        ));
    }

    public function print(Request $request)
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

        $products = $query->orderBy('created_at', 'DESC')->get();
        
        return view('reports.print.inventory-print', compact(
            'products',
            'timePeriod'
        ));
    }

}