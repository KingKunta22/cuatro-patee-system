<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductMovementReportsController extends Controller
{
    public function index(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        // Get sales data for outflow
        $sales = Sale::with(['items', 'items.productBatch.product'])
                    ->orderBy('sale_date', 'DESC')
                    ->get();
        
        // Get products with their batches for inflow
        $products = Product::with(['brand', 'category', 'batches', 'batches.purchaseOrder'])
                            ->orderBy('created_at', 'DESC')
                            ->get();

        // Prefetch total sold quantities per batch to reconstruct original inflow quantities
        $soldByBatch = SaleItem::select('product_batch_id', DB::raw('SUM(quantity) as qty_sold'))
            ->groupBy('product_batch_id')
            ->pluck('qty_sold', 'product_batch_id');
        
        // Combine data into movements array
        $movements = $this->combineMovements($sales, $products, $soldByBatch);
        
        // Strict date-time sorting (newest first). Expect full timestamps for both inflow/outflow.
        usort($movements, function($a, $b) {
            $cmp = strtotime($b['date']) <=> strtotime($a['date']);
            if ($cmp !== 0) return $cmp;
            // Secondary tie-breaker: outflow after inflow for identical timestamps
            return strcmp($a['type'], $b['type']);
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

        public function print(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        // Get sales data for outflow
        $sales = Sale::with(['items', 'items.productBatch.product'])
                    ->orderBy('sale_date', 'DESC')
                    ->get();
        
        // Get products with their batches for inflow
        $products = Product::with(['brand', 'category', 'batches', 'batches.purchaseOrder'])
                            ->orderBy('created_at', 'DESC')
                            ->get();

        // Prefetch total sold quantities per batch to reconstruct original inflow quantities
        $soldByBatch = SaleItem::select('product_batch_id', DB::raw('SUM(quantity) as qty_sold'))
            ->groupBy('product_batch_id')
            ->pluck('qty_sold', 'product_batch_id');
        
        // Combine data into movements array
        $movements = $this->combineMovements($sales, $products, $soldByBatch);
        
        // Strict date-time sorting (newest first)
        usort($movements, function($a, $b) {
            $cmp = strtotime($b['date']) <=> strtotime($a['date']);
            if ($cmp !== 0) return $cmp;
            return strcmp($a['type'], $b['type']);
        });
        
        // Calculate stats
        $stats = $this->calculateStats($timePeriod);
        
        // For print, we want all data without pagination
        $perPage = 1000; // Large number to show most data
        $currentPage = 1;
        $offset = ($currentPage - 1) * $perPage;
        $printMovements = array_slice($movements, $offset, $perPage);
        
        $movementsPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $printMovements,
            count($movements),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(), 
                'query' => $request->query(),
            ]
        );

        return view('reports.print.product-movement-print', array_merge($stats, [
            'movementsPaginator' => $movementsPaginator,
            'timePeriod' => $timePeriod,
            'movements' => $printMovements // Also pass raw movements for easier looping
        ]));
    }


    
    private function combineMovements($sales, $products, $soldByBatch)
    {
        $movements = [];

        // Process sales (outflow) with precise timestamps (use sale created_at)
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productName = $this->getProductNameForSaleItem($item);
                
                $movements[] = [
                    'date' => $sale->created_at, // precise timestamp for ordering
                    'reference_number' => $sale->invoice_number,
                    'product_name' => $productName,
                    'quantity' => -$item->quantity,
                    'type' => 'outflow',
                    'remarks' => 'Sale'
                ];
            }
        }
        
        // Process product additions as INDIVIDUAL batch additions
        foreach ($products as $product) {
            foreach ($product->batches as $batch) {
                $source = 'Manual Addition';
                $referenceNumber = 'Manually added (' . ($product->productSKU ?? 'No SKU') . ')';
                
                if ($batch->purchase_order_id && $batch->purchaseOrder) {
                    $source = 'Purchase Order';
                    $referenceNumber = $batch->purchaseOrder->orderNumber;
                }
                
                // Reconstruct original inflow: current qty + total sold from this batch
                $soldQty = (int) ($soldByBatch[$batch->id] ?? 0);
                $originalQty = (int) $batch->quantity + $soldQty;

                // Exclude defective/bad items from inflow: subtract bad items tied to this PO item
                if ($batch->purchase_order_item_id) {
                    $badCount = \App\Models\BadItem::where('purchase_order_item_id', $batch->purchase_order_item_id)
                        ->sum('item_count');
                    $originalQty = max(0, $originalQty - (int) $badCount);
                }

                $movements[] = [
                    'date' => $batch->created_at, // precise timestamp when batch was added
                    'reference_number' => $referenceNumber,
                    'product_name' => $product->productName,
                    'quantity' => $originalQty, // immutable original inflow for display
                    'type' => 'inflow',
                    'remarks' => $source
                ];
            }
        }
        
        return $movements;
    }

    private function getProductNameForSaleItem($item)
    {
        if (!empty($item->product_name)) {
            return preg_replace('/\s*\([^)]*\)\s*$/', '', $item->product_name);
        }
        
        if ($item->productBatch && $item->productBatch->product && !empty($item->productBatch->product->productName)) {
            return preg_replace('/\s*\([^)]*\)\s*$/', '', $item->productBatch->product->productName);
        }
        
        return 'Product Batch #' . $item->product_batch_id;
    }
    
    private function calculateStats($timePeriod)
    {
        // Keep your existing stats calculation (this is correct)
        $stockInQuery = ProductBatch::query();
        if ($timePeriod !== 'all') {
            $this->applyTimeFilterToQuery($stockInQuery, $timePeriod, 'created_at');
        }
        $totalStockIn = $stockInQuery->sum('quantity');

        $stockOutQuery = SaleItem::query();
        if ($timePeriod !== 'all') {
            $this->applyTimeFilterToQuery($stockOutQuery, $timePeriod, 'created_at');
        }
        $totalStockOut = $stockOutQuery->sum('quantity');
        
        $revenueQuery = Sale::query();
        $costQuery = SaleItem::join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id');
        
        if ($timePeriod !== 'all') {
            $this->applyTimeFilterToQuery($revenueQuery, $timePeriod, 'sale_date');
            $this->applyTimeFilterToQuery($costQuery, $timePeriod, 'sale_items.created_at');
        }
        
        $totalRevenue = $revenueQuery->sum('total_amount');
        $totalCost = $costQuery->sum(DB::raw('sale_items.quantity * product_batches.cost_price'));
        $totalProfit = $totalRevenue - $totalCost;

        return compact('totalStockIn', 'totalStockOut', 'totalRevenue', 'totalCost', 'totalProfit');
    }
    
    private function applyTimeFilterToQuery($query, $timePeriod, $dateField)
    {
        switch ($timePeriod) {
            case 'today':
                $query->whereDate($dateField, today());
                break;
            case 'lastWeek':
                $query->whereBetween($dateField, [now()->subDays(7), now()]);
                break;
            case 'lastMonth':
                $query->whereBetween($dateField, [now()->subDays(30), now()]);
                break;
        }
    }
    
    private function paginateMovements($movements, $request)
    {
        $perPage = 10;
        $currentPage = $request->get('product_page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedMovements = array_slice($movements, $offset, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedMovements,
            count($movements),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(), 
                'query' => $request->query(),
                'pageName' => 'product_page'
            ]
        );
    }
}