<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Admin check
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }
        

        $timePeriod = $request->timePeriod ?? 'all';
        
        // Data for inventory tab with time period filtering
        $inventories = $this->getInventoryData($timePeriod);

        // Data for PO tab with time period filtering
        $purchaseOrders = $this->getPurchaseOrderData($timePeriod);

        // Data for Sales tab with time period filtering
        $sales = $this->getSalesData($timePeriod);

        // Calculate stock totals with time period filtering
        list($totalStockIn, $totalStockOut) = $this->getStockTotals($timePeriod);

        // Calculate revenue stats with time period filtering
        list($totalRevenue, $totalCost, $totalProfit) = $this->getRevenueStats($timePeriod);

        // Get product movements data
        $productMovements = $this->getProductMovementsData($timePeriod);

        return view('reports', compact(
            'inventories', 
            'purchaseOrders', 
            'sales', 
            'totalStockIn', 
            'totalStockOut', 
            'totalRevenue', 
            'totalCost', 
            'totalProfit',
            'productMovements',
            'timePeriod'
        ));
    }

    private function getInventoryData($timePeriod)
    {

        $query = Product::with(['brand', 'category', 'batches']);
        
        $this->applyTimeFilter($query, $timePeriod, 'created_at');
        
        return $query->orderBy('created_at', 'DESC')
                    ->paginate(10, ['*'], 'inventory_page');
    }



    private function getPurchaseOrderData($timePeriod)
    {

        $query = PurchaseOrder::with([
            'supplier', 
            'items', 
            'items.productBatches', // Changed from items.inventory
            'items.badItems', 
            'notes', 
            'deliveries'
        ])
        ->whereHas('deliveries', function($query) {
            $query->where(function($q) {
                $q->where('orderStatus', 'Delivered')
                ->orWhere('orderStatus', 'Cancelled')
                ->orWhere(function($confirmedQuery) {
                    $confirmedQuery->where('orderStatus', 'Confirmed')
                                    ->whereHas('purchaseOrder', function($poQuery) {
                                        $poQuery->where('deliveryDate', '<', now()->startOfDay());
                                    });
                });
            });
        });
        
        $this->applyTimeFilter($query, $timePeriod, 'created_at');
        
        return $query->orderByDesc(function($query) {
                $query->select('status_updated_at')
                    ->from('deliveries')
                    ->whereColumn('purchase_orders.id', 'deliveries.purchase_order_id')
                    ->orderBy('status_updated_at', 'desc')
                    ->limit(1);
            })
            ->paginate(10, ['*'], 'po_page');
    }


    private function getSalesData($timePeriod)
    {
        $query = Sale::with(['items', 'items.productBatch.product']);
        
        $this->applyTimeFilter($query, $timePeriod, 'sale_date');
        
        return $query->orderBy('sale_date', 'DESC')
                    ->paginate(10, ['*'], 'sales_page');
    }


    private function getStockTotals($timePeriod)
    {
        // Stock in: Sum of all product batch quantities
        $stockInQuery = ProductBatch::query();
        $this->applyTimeFilter($stockInQuery, $timePeriod, 'created_at');
        $totalStockIn = $stockInQuery->sum('quantity');
        
        // Stock out: Sum of all sale items quantities
        $stockOutQuery = SaleItem::query();
        $this->applyTimeFilter($stockOutQuery, $timePeriod, 'created_at');
        $totalStockOut = $stockOutQuery->sum('quantity');
        
        return [$totalStockIn, $totalStockOut];
    }


    private function getRevenueStats($timePeriod)
    {

        $revenueQuery = Sale::query();
        $costQuery = SaleItem::join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id');
        
        $this->applyTimeFilter($revenueQuery, $timePeriod, 'sale_date');
        $this->applyTimeFilter($costQuery, $timePeriod, 'sale_date', 'sale');
        
        $totalRevenue = $revenueQuery->sum('total_amount');
        $totalCost = $costQuery->sum(DB::raw('sale_items.quantity * product_batches.cost_price'));
        $totalProfit = $totalRevenue - $totalCost;
        
        return [$totalRevenue, $totalCost, $totalProfit];
    }


    private function applyTimeFilter($query, $timePeriod, $dateField, $relation = null)
    {
        

        if ($timePeriod !== 'all') {
            if ($relation) {
                // For relationships, use whereHas with the time condition
                $query->whereHas($relation, function($q) use ($timePeriod, $dateField) {
                    $this->applyTimeCondition($q, $timePeriod, $dateField);
                });
            } else {
                // For direct queries, apply the time condition directly
                $this->applyTimeCondition($query, $timePeriod, $dateField);
            }
        }
    }


    private function applyTimeCondition($query, $timePeriod, $dateField)
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


    private function getProductMovementsData($timePeriod)
    {
        

        // Get sales data for outflow with time filtering
        $salesQuery = Sale::with(['items', 'items.productBatch.product']);
        $this->applyTimeFilter($salesQuery, $timePeriod, 'sale_date');
        $sales = $salesQuery->orderBy('sale_date', 'DESC')->get();
        
        // Get product additions for inflow with time filtering
        $productsQuery = Product::with(['brand', 'category', 'batches']);
        $this->applyTimeFilter($productsQuery, $timePeriod, 'created_at');
        $products = $productsQuery->orderBy('created_at', 'DESC')->get();
        
        $movements = [];
        
        // Process sales (outflow)
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productName = $item->product_name;
                
                // Clean up product name by removing SKU if it exists
                if (!empty($productName)) {
                    $productName = preg_replace('/\s*\([^)]*\)\s*$/', '', $productName);
                } elseif ($item->product && !empty($item->product->productName)) {
                    $productName = preg_replace('/\s*\([^)]*\)\s*$/', '', $item->product->productName);
                } else {
                    $productName = 'Product #' . $item->product_id;
                }
                
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
        
        // Process product additions (inflow) - ACTUAL product additions
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
        
        // Sort movements by date (newest first)
        usort($movements, function($a, $b) {
            $dateCompare = strtotime($b['date']) <=> strtotime($a['date']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return $b['reference_number'] <=> $a['reference_number'];
        });
        
        // Paginate movements
        $perPage = 10;
        $currentPage = request()->get('product_page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedMovements = array_slice($movements, $offset, $perPage);
        
        $movementsPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedMovements,
            count($movements),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(), 
                'query' => request()->query(),
                'pageName' => 'product_page'
            ]
        );
        
        // Use the same time-filtered stats methods as other tabs
        list($totalStockIn, $totalStockOut) = $this->getStockTotals($timePeriod);
        list($totalRevenue, $totalCost, $totalProfit) = $this->getRevenueStats($timePeriod);
        
        return [
            'movementsPaginator' => $movementsPaginator,
            'totalStockIn' => $totalStockIn,
            'totalStockOut' => $totalStockOut,
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit
        ];
    }
}