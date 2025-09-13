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
        
        // Data for inventory tab
        $inventories = Inventory::with(['category', 'brand'])
                        ->orderBy('created_at', 'DESC')
                        ->paginate(10, ['*'], 'inventory_page');


        // Data for PO tab
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items', 'items.inventory', 'items.badItems', 'notes', 'deliveries'])
            ->whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->orderByDesc(function($query) {
                $query->select('status_updated_at')
                    ->from('deliveries')
                    ->whereColumn('purchase_orders.id', 'deliveries.purchase_order_id')
                    ->where('orderStatus', 'Delivered')
                    ->orderBy('status_updated_at', 'desc')
                    ->limit(1);
            })
            ->paginate(10, ['*'], 'po_page');


        // Data for Sales tab
        $sales = Sale::with(['items', 'items.inventory'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10, ['*'], 'sales_page');




        // Calculate stock totals for inventory reports
        $totalStockIn = PurchaseOrderItem::whereHas('purchaseOrder.deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->sum('quantity');

        $totalStockOut = SaleItem::sum('quantity');

        // Calculate revenue stats for sales reports
        $totalRevenue = SaleItem::sum(DB::raw('quantity * unit_price'));
        $totalCost = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
            ->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        $totalProfit = $totalRevenue - $totalCost;

        // Get product movements data for the default tab
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
            'productMovements', // Pass the product movements data
            'timePeriod'
        ));
    }

    private function getProductMovementsData($timePeriod)
    {
        // Get sales data for outflow
        $sales = Sale::with(['items', 'items.inventory'])
            ->orderBy('sale_date', 'DESC')
            ->get();
        
        // Get inventory changes for inflow (ACTUAL inventory additions)
        $inventoryChanges = Inventory::with(['category', 'brand'])
                            ->orderBy('created_at', 'DESC')
                            ->get();
        
        $movements = [];
        
        // Process sales (outflow)
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $productName = $item->product_name;
                if (empty($productName) && $item->inventory) {
                    $productName = $item->inventory->productName;
                }
                if (empty($productName)) {
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
                
                // Check if from purchase order by matching product names
                $purchaseOrderItem = PurchaseOrderItem::where('productName', $inventory->productName)->first();
                    
                if ($purchaseOrderItem && $purchaseOrderItem->purchaseOrder) {
                    $po = $purchaseOrderItem->purchaseOrder;
                    if ($po->deliveries()->where('orderStatus', 'Delivered')->exists()) {
                        $source = 'Purchase Order: ' . $po->orderNumber;
                    }
                }
                
                $movements[] = [
                    'date' => $inventory->created_at,
                    'reference_number' => 'INV-' . $inventory->id,
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
        
        // Update stats calculations to use actual inventory data
        $totalStockIn = Inventory::sum('productStock');
        $totalStockOut = SaleItem::sum('quantity');
        $totalRevenue = SaleItem::sum(DB::raw('quantity * unit_price'));
        $totalCost = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
            ->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        $totalProfit = $totalRevenue - $totalCost;
        
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