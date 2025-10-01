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
        $timePeriod = $request->timePeriod ?? 'today';
        
        // Data for inventory tab with time period filtering
        $inventories = $this->getInventoryData($timePeriod);

        // Data for PO tab with time period filtering
        $purchaseOrders = $this->getPurchaseOrderData($timePeriod);

        // Data for Sales tab with time period filtering - ONLY FOR ADMINS
        $sales = $this->canViewSalesReports() ? $this->getSalesData($timePeriod) : null;

        // Calculate revenue stats with time period filtering - ONLY FOR ADMINS
        if ($this->canViewSalesReports()) {
            list($totalRevenue, $totalCost, $totalProfit) = $this->getRevenueStats($timePeriod);
        } else {
            $totalRevenue = $totalCost = $totalProfit = 0;
        }

        // Get product movements data (this already includes stock totals)
        $productMovements = $this->getProductMovementsData($timePeriod);

        return view('reports', compact(
            'inventories', 
            'purchaseOrders', 
            'sales', 
            'totalRevenue', 
            'totalCost', 
            'totalProfit',
            'productMovements',
            'timePeriod'
        ));
    }

    private function getInventoryData($timePeriod)
    {
        $query = Product::with([
            'brand', 
            'category', 
            'batches'
        ]);
        
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
        $query = Sale::with([
            'items', 
            'items.productBatch.product',
            'user'
        ]);
        
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
        $costQuery = SaleItem::join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('purchase_orders', 'product_batches.purchase_order_id', '=', 'purchase_orders.id')
            ->join('deliveries', 'purchase_orders.id', '=', 'deliveries.purchase_order_id')
            ->where('deliveries.orderStatus', 'Delivered'); // ← CRITICAL FILTER
        
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
        // Get sales data for outflow with time filtering (fetch with items and batch)
        $salesQuery = Sale::with(['items', 'items.productBatch.product']);
        $this->applyTimeFilter($salesQuery, $timePeriod, 'sale_date');
        $sales = $salesQuery->orderBy('created_at', 'DESC')->get();

        // Get products with their batches for inflow with time filtering
        $productsQuery = Product::with(['brand', 'category', 'batches', 'batches.purchaseOrder']);
        $this->applyTimeFilter($productsQuery, $timePeriod, 'created_at');
        $products = $productsQuery->orderBy('created_at', 'DESC')->get();

        // Prefetch total sold quantities per batch to reconstruct original inflow
        $soldByBatch = SaleItem::select('product_batch_id', DB::raw('SUM(quantity) as qty_sold'))
            ->groupBy('product_batch_id')
            ->pluck('qty_sold', 'product_batch_id');

        $movements = [];

        // Outflow movements: use precise timestamp for ordering
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productName = $item->product_name;
                if (!empty($productName)) {
                    $productName = preg_replace('/\s*\([^)]*\)\s*$/', '', $productName);
                } elseif ($item->product && !empty($item->product->productName)) {
                    $productName = preg_replace('/\s*\([^)]*\)\s*$/', '', $item->product->productName);
                } else {
                    $productName = 'Product #' . ($item->product_id ?? $item->product_batch_id);
                }

                $movements[] = [
                    'date' => $sale->created_at,
                    'reference_number' => $sale->invoice_number,
                    'product_name' => $productName,
                    'quantity' => -$item->quantity,
                    'type' => 'outflow',
                    'remarks' => 'Sale'
                ];
            }
        }

        // Inflow movements: reconstruct immutable original batch quantity
        foreach ($products as $product) {
            foreach ($product->batches as $batch) {
                $source = 'Manual Addition';
                $referenceNumber = 'Manually added (' . ($product->productSKU ?? 'No SKU') . ')';
                if ($batch->purchase_order_id && $batch->purchaseOrder) {
                    $source = 'Purchase Order';
                    $referenceNumber = $batch->purchaseOrder->orderNumber;
                }

                $soldQty = (int) ($soldByBatch[$batch->id] ?? 0);
                $originalQty = (int) $batch->quantity + $soldQty;

                // Exclude defective/bad items from inflow
                if ($batch->purchase_order_item_id) {
                    $badCount = \App\Models\BadItem::where('purchase_order_item_id', $batch->purchase_order_item_id)
                        ->sum('item_count');
                    $originalQty = max(0, $originalQty - (int) $badCount);
                }

                $movements[] = [
                    'date' => $batch->created_at,
                    'reference_number' => $referenceNumber,
                    'product_name' => $product->productName,
                    'quantity' => $originalQty,
                    'type' => 'inflow',
                    'remarks' => $source
                ];
            }
        }

        // Strict newest-first sort by timestamp only
        usort($movements, function($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
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

    private function canViewSalesReports()
    {
        return Auth::user()->role === 'admin';
    }

}