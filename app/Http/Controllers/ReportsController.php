<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SaleItem;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
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
        $query = Inventory::with(['category', 'brand']);
        
        $this->applyTimeFilter($query, $timePeriod, 'created_at');
        
        return $query->orderBy('created_at', 'DESC')
                    ->paginate(10, ['*'], 'inventory_page');
    }



    private function getPurchaseOrderData($timePeriod)
    {
        $query = PurchaseOrder::with(['supplier', 'items', 'items.inventory', 'items.badItems', 'notes', 'deliveries'])
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
        $query = Sale::with(['items', 'items.inventory']);
        
        $this->applyTimeFilter($query, $timePeriod, 'sale_date');
        
        return $query->orderBy('sale_date', 'DESC')
                    ->paginate(10, ['*'], 'sales_page');
    }




    private function getStockTotals($timePeriod)
    {
        $stockInQuery = PurchaseOrderItem::whereHas('purchaseOrder.deliveries', function($query) {
            $query->where('orderStatus', 'Delivered');
        });
        
        $stockOutQuery = SaleItem::query();
        
        $this->applyTimeFilter($stockInQuery, $timePeriod, 'purchase_order_items.created_at');
        $this->applyTimeFilter($stockOutQuery, $timePeriod, 'sale_items.created_at', 'sale');
        
        $totalStockIn = $stockInQuery->sum('quantity');
        $totalStockOut = $stockOutQuery->sum('quantity');
        
        return [$totalStockIn, $totalStockOut];
    }


    private function getRevenueStats($timePeriod)
    {
        $revenueQuery = Sale::query();
        $costQuery = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id');
        
        $this->applyTimeFilter($revenueQuery, $timePeriod, 'sale_date');
        $this->applyTimeFilter($costQuery, $timePeriod, 'sale_date', 'sale');
        
        $totalRevenue = $revenueQuery->sum('total_amount');
        $totalCost = $costQuery->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
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
        $salesQuery = Sale::with(['items', 'items.inventory']);
        $this->applyTimeFilter($salesQuery, $timePeriod, 'sale_date');
        $sales = $salesQuery->orderBy('sale_date', 'DESC')->get();
        
        // Get inventory changes for inflow with time filtering
        $inventoryQuery = Inventory::with(['category', 'brand']);
        $this->applyTimeFilter($inventoryQuery, $timePeriod, 'created_at');
        $inventoryChanges = $inventoryQuery->orderBy('created_at', 'DESC')->get();
        
        $movements = [];
        
        // Process sales (outflow)
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productName = $item->product_name;
                
                // Clean up product name by removing SKU if it exists
                if (!empty($productName)) {
                    $productName = preg_replace('/\s*\([^)]*\)\s*$/', '', $productName);
                } elseif ($item->inventory && !empty($item->inventory->productName)) {
                    $productName = preg_replace('/\s*\([^)]*\)\s*$/', '', $item->inventory->productName);
                } else {
                    $productName = 'Product #' . $item->inventory_id;
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
        
        // Process inventory additions (inflow) - ACTUAL inventory changes
        foreach ($inventoryChanges as $inventory) {
            // Only show as inflow if product was actually added to inventory
            if ($inventory->productStock > 0) {
                $source = 'Manual Addition';
                $referenceNumber = 'Manually added (' . ($inventory->productSKU ?? 'No SKU') . ')';
                
                // Check if from purchase order by matching product names
                $purchaseOrderItem = PurchaseOrderItem::where('productName', $inventory->productName)->first();
                    
                if ($purchaseOrderItem && $purchaseOrderItem->purchaseOrder) {
                    $po = $purchaseOrderItem->purchaseOrder;
                    // Only count as PO source if it was actually delivered
                    if ($po->deliveries()->where('orderStatus', 'Delivered')->exists()) {
                        $source = 'Purchase Order';
                        $referenceNumber = $po->orderNumber; // Use PO number as reference
                    }
                }
                
                $movements[] = [
                    'date' => $inventory->created_at,
                    'reference_number' => $referenceNumber,
                    'product_name' => $inventory->productName,
                    'quantity' => $inventory->productStock,
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