<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductMovementReportsController extends Controller
{
    public function index(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        // Get sales data for outflow with new structure
        $sales = Sale::with(['items', 'items.productBatch.product'])
                    ->orderBy('sale_date', 'DESC')
                    ->get();
        
        // Get product additions for inflow (ACTUAL product additions from batches)
        $products = Product::with(['brand', 'category', 'batches'])
                            ->orderBy('created_at', 'DESC')
                            ->get();
        
        // Combine data into movements array
        $movements = $this->combineMovements($sales, $products);
        
        // Sort movements by date (newest first)
        usort($movements, function($a, $b) {
            // First compare dates
            $dateCompare = strtotime($b['date']) <=> strtotime($a['date']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            
            // If dates are equal, use reference number as secondary sort
            return $b['reference_number'] <=> $a['reference_number'];
        });
        
        // Calculate stats with new structure
        $stats = $this->calculateStats($timePeriod);
        
        // Paginate movements
        $movementsPaginator = $this->paginateMovements($movements, $request);
        
        return view('reports.product-movement-reports', array_merge($stats, [
            'movementsPaginator' => $movementsPaginator,
            'timePeriod' => $timePeriod
        ]));
    }
    
    private function combineMovements($sales, $products)
    {
        $movements = [];

        // Process sales (outflow)
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productName = $this->getProductNameForSaleItem($item);
                
                $movements[] = [
                    'date' => $sale->sale_date,
                    'reference_number' => $sale->invoice_number,
                    'product_name' => $productName,
                    'quantity' => -$item->quantity,
                    'type' => 'outflow',
                    'remarks' => 'Sale'
                ];
            }
        }
        
        // Process product additions (inflow) - ACTUAL product additions from batches
        foreach ($products as $product) {
            // Calculate total stock from batches
            $totalStock = $product->batches->sum('quantity');
            
            // Only show as inflow if product has stock
            if ($totalStock > 0) {
                $source = 'Manual Addition';
                $referenceNumber = 'Manually added (' . ($product->productSKU ?? 'No SKU') . ')';
                
                // Check if from purchase order by checking batches
                $poBatch = $product->batches->firstWhere('purchase_order_id', '!=', null);
                
                if ($poBatch && $poBatch->purchaseOrder) {
                    $po = $poBatch->purchaseOrder;
                    $source = 'Purchase Order';
                    $referenceNumber = $po->orderNumber;
                }
                
                $movements[] = [
                    'date' => $product->created_at,
                    'reference_number' => $referenceNumber,
                    'product_name' => $product->productName,
                    'quantity' => $totalStock,
                    'type' => 'inflow',
                    'remarks' => $source
                ];
            }
        }
        
        return $movements;
    }

    // NEW HELPER METHOD FOR SALE ITEMS with new structure
    private function getProductNameForSaleItem($item)
    {
        // Priority 1: Use sale item's product_name if available (without SKU)
        if (!empty($item->product_name)) {
            return preg_replace('/\s*\([^)]*\)\s*$/', '', $item->product_name);
        }
        
        // Priority 2: Use product batch's product name if relationship exists
        if ($item->productBatch && $item->productBatch->product && !empty($item->productBatch->product->productName)) {
            return preg_replace('/\s*\([^)]*\)\s*$/', '', $item->productBatch->product->productName);
        }
        
        // Priority 3: Fallback to product batch ID
        return 'Product Batch #' . $item->product_batch_id;
    }
    
    private function calculateStats($timePeriod)
    {
        // Calculate total stock in (from product batches)
        $stockInQuery = ProductBatch::query();
        if ($timePeriod !== 'all') {
            $this->applyTimeFilterToQuery($stockInQuery, $timePeriod, 'created_at');
        }
        $totalStockIn = $stockInQuery->sum('quantity');

        // Calculate total stock out (from sales)
        $stockOutQuery = SaleItem::query();
        if ($timePeriod !== 'all') {
            $this->applyTimeFilterToQuery($stockOutQuery, $timePeriod, 'created_at');
        }
        $totalStockOut = $stockOutQuery->sum('quantity');
        
        // Calculate revenue and cost with new structure
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
    
    // Helper method for time filtering
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