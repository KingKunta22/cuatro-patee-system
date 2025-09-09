<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductMovementReportsController extends Controller
{
    public function index(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        // Get sales data for outflow (items sold)
        $salesQuery = Sale::with(['items', 'items.inventory']);
        
        // Get purchase orders data for inflow (items received)
        $poQuery = PurchaseOrder::with(['items', 'items.inventory'])
            ->whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            });
        
        // Apply time period filter to both queries
        if ($timePeriod !== 'all') {
            $this->applyTimeFilter($salesQuery, $poQuery, $timePeriod);
        }
        
        $sales = $salesQuery->orderBy('sale_date', 'DESC')->get();
        $purchaseOrders = $poQuery->orderBy('created_at', 'DESC')->get();
        
        // Combine data into movements array
        $movements = $this->combineMovements($sales, $purchaseOrders);
        
        // Sort movements by date (newest first)
        usort($movements, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });
        
        // Calculate stats
        $stats = $this->calculateStats($timePeriod);
        
        // Paginate movements
        $movementsPaginator = $this->paginateMovements($movements, $request);
        
        return view('reports.product-movement-reports', array_merge($stats, [
            'movementsPaginator' => $movementsPaginator,
            'timePeriod' => $timePeriod
        ]));
    }
    
    private function applyTimeFilter($salesQuery, $poQuery, $timePeriod)
    {
        switch ($timePeriod) {
            case 'today':
                $salesQuery->whereDate('sale_date', today());
                $poQuery->whereDate('created_at', today());
                break;
            case 'lastWeek':
                $salesQuery->whereBetween('sale_date', [now()->subDays(7), now()]);
                $poQuery->whereBetween('created_at', [now()->subDays(7), now()]);
                break;
            case 'lastMonth':
                $salesQuery->whereBetween('sale_date', [now()->subDays(30), now()]);
                $poQuery->whereBetween('created_at', [now()->subDays(30), now()]);
                break;
        }
    }
    
    private function combineMovements($sales, $purchaseOrders)
    {
        $movements = [];
        
        // Process sales (outflow) - negative quantities
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $movements[] = [
                    'date' => $sale->sale_date,
                    'reference_number' => $sale->invoice_number,
                    'product_name' => $item->inventory->productName ?? $item->product_name ?? 'N/A',
                    'quantity' => -$item->quantity, // Negative for outflow
                    'type' => 'outflow',
                    'remarks' => 'Sale'
                ];
            }
        }
        
        // Process purchase orders (inflow) - positive quantities
        foreach ($purchaseOrders as $po) {
            foreach ($po->items as $item) {
                $movements[] = [
                    'date' => $po->created_at,
                    'reference_number' => $po->orderNumber,
                    'product_name' => $item->productName,
                    'quantity' => $item->quantity, // Positive for inflow
                    'type' => 'inflow',
                    'remarks' => 'Purchase Order'
                ];
            }
        }
        
        return $movements;
    }
    
    private function calculateStats($timePeriod)
    {
        // Calculate total stock in (from purchase orders)
        $stockInQuery = PurchaseOrderItem::whereHas('purchaseOrder.deliveries', function($query) {
            $query->where('orderStatus', 'Delivered');
        });

        // Calculate total stock out (from sales)
        $stockOutQuery = SaleItem::query();

        // Apply time period filters if needed
        if ($timePeriod !== 'all') {
            $this->applyStatsTimeFilter($stockInQuery, $stockOutQuery, $timePeriod);
        }

        $totalStockIn = $stockInQuery->sum('quantity');
        $totalStockOut = $stockOutQuery->sum('quantity');
        
        // Calculate revenue and cost
        $revenueQuery = SaleItem::query();
        $costQuery = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id');

        if ($timePeriod !== 'all') {
            $this->applyStatsTimeFilter(null, $revenueQuery, $timePeriod, 'sale');
            $this->applyStatsTimeFilter(null, $costQuery, $timePeriod, 'sale');
        }

        $totalRevenue = $revenueQuery->sum(DB::raw('quantity * unit_price'));
        $totalCost = $costQuery->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        $totalProfit = $totalRevenue - $totalCost;

        return compact('totalStockIn', 'totalStockOut', 'totalRevenue', 'totalCost', 'totalProfit');
    }
    
    private function applyStatsTimeFilter($stockInQuery, $otherQuery, $timePeriod, $relation = 'sale')
    {
        $dateField = $relation === 'sale' ? 'sale_date' : 'created_at';
        
        switch ($timePeriod) {
            case 'today':
                if ($stockInQuery) {
                    $stockInQuery->whereDate('created_at', today());
                }
                $otherQuery->whereHas($relation, function($q) use ($dateField) {
                    $q->whereDate($dateField, today());
                });
                break;
            case 'lastWeek':
                if ($stockInQuery) {
                    $stockInQuery->whereBetween('created_at', [now()->subDays(7), now()]);
                }
                $otherQuery->whereHas($relation, function($q) use ($dateField) {
                    $q->whereBetween($dateField, [now()->subDays(7), now()]);
                });
                break;
            case 'lastMonth':
                if ($stockInQuery) {
                    $stockInQuery->whereBetween('created_at', [now()->subDays(30), now()]);
                }
                $otherQuery->whereHas($relation, function($q) use ($dateField) {
                    $q->whereBetween($dateField, [now()->subDays(30), now()]);
                });
                break;
        }
    }
    
    private function paginateMovements($movements, $request)
    {
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedMovements = array_slice($movements, $offset, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedMovements,
            count($movements),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}