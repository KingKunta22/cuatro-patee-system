<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductMovementReportsController extends Controller
{
    public function index(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        // Get sales data
        $sales = Sale::with(['items', 'items.inventory'])
                    ->orderBy('sale_date', 'DESC')
                    ->get();
        
        // Get purchase orders data
        $purchaseOrders = PurchaseOrder::with(['items', 'items.inventory'])
                    ->whereHas('deliveries', function($query) {
                        $query->where('orderStatus', 'Delivered');
                    })
                    ->orderBy('created_at', 'DESC')
                    ->get();
        
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
    
    private function combineMovements($sales, $purchaseOrders)
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
        
        // Process purchase orders (inflow)
        foreach ($purchaseOrders as $po) {
            foreach ($po->items as $item) {
                $productName = $this->getProductNameForPOItem($item);
                
                $movements[] = [
                    'date' => $po->created_at,
                    'reference_number' => $po->orderNumber,
                    'product_name' => $productName,
                    'quantity' => $item->quantity,
                    'type' => 'inflow',
                    'remarks' => 'Purchase Order'
                ];
            }
        }
        
        return $movements;
    }

// NEW HELPER METHOD FOR SALE ITEMS
private function getProductNameForSaleItem($item)
{
    // Priority 1: Use sale item's product_name if available
    if (!empty($item->product_name)) {
        return $item->product_name;
    }
    
    // Priority 2: Use inventory productName if relationship exists
    if ($item->inventory && !empty($item->inventory->productName)) {
        return $item->inventory->productName;
    }
    
    // Priority 3: Fallback to inventory ID
    return 'Product #' . $item->inventory_id;
}

// NEW HELPER METHOD FOR PO ITEMS  
private function getProductNameForPOItem($item)
{
    // Priority 1: Use PO item's productName (should always exist)
    if (!empty($item->productName)) {
        return $item->productName;
    }
    
    // Priority 2: Use inventory productName if relationship exists
    if ($item->inventory && !empty($item->inventory->productName)) {
        return $item->inventory->productName;
    }
    
    // Priority 3: Fallback to item ID
    return 'Product from PO Item #' . $item->id;
}
    
    private function calculateStats($timePeriod)
    {
        // Calculate total stock in (from purchase orders)
        $stockInQuery = PurchaseOrderItem::whereHas('purchaseOrder.deliveries', function($query) {
            $query->where('orderStatus', 'Delivered');
        });

        // Calculate total stock out (from sales)
        $stockOutQuery = SaleItem::query();

        $totalStockIn = $stockInQuery->sum('quantity');
        $totalStockOut = $stockOutQuery->sum('quantity');
        
        // Calculate revenue and cost
        $revenueQuery = SaleItem::query();
        $costQuery = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id');

        $totalRevenue = $revenueQuery->sum(DB::raw('quantity * unit_price'));
        $totalCost = $costQuery->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        $totalProfit = $totalRevenue - $totalCost;

        return compact('totalStockIn', 'totalStockOut', 'totalRevenue', 'totalCost', 'totalProfit');
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