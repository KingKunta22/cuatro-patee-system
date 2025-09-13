<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductMovementReportsController extends Controller
{
    public function index(Request $request)
    {
        $timePeriod = $request->timePeriod ?? 'all';
        
        // Get sales data for outflow
        $sales = Sale::with(['items', 'items.inventory'])
                    ->orderBy('sale_date', 'DESC')
                    ->get();
        
        // Get inventory changes for inflow (ACTUAL inventory additions)
        $inventoryChanges = Inventory::with(['category', 'brand'])
                            ->orderBy('created_at', 'DESC')
                            ->get();
        
        // Combine data into movements array
        $movements = $this->combineMovements($sales, $inventoryChanges);
        
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
        
        // Calculate stats
        $stats = $this->calculateStats($timePeriod);
        
        // Paginate movements
        $movementsPaginator = $this->paginateMovements($movements, $request);
        
        return view('reports.product-movement-reports', array_merge($stats, [
            'movementsPaginator' => $movementsPaginator,
            'timePeriod' => $timePeriod
        ]));
    }
    
    private function combineMovements($sales, $inventoryChanges)
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
        
        // Process inventory additions (inflow) - ACTUAL inventory changes
        foreach ($inventoryChanges as $inventory) {
            // Only show as inflow if product was actually added to inventory
            if ($inventory->productStock > 0) {
                $movements[] = [
                    'date' => $inventory->created_at,
                    'reference_number' => 'INV-' . $inventory->id,
                    'product_name' => $inventory->productName,
                    'quantity' => $inventory->productStock,
                    'type' => 'inflow',
                    'remarks' => $this->getInventorySource($inventory)
                ];
            }
        }
        
        return $movements;
    }

    // Determine the source of inventory addition
    private function getInventorySource($inventory)
    {
        // Check if this inventory came from a purchase order by matching product names
        $purchaseOrderItem = PurchaseOrderItem::where('productName', $inventory->productName)->first();
            
        if ($purchaseOrderItem && $purchaseOrderItem->purchaseOrder) {
            $po = $purchaseOrderItem->purchaseOrder;
            // Check if the PO was actually delivered
            if ($po->deliveries()->where('orderStatus', 'Delivered')->exists()) {
                return 'Purchase Order: ' . $po->orderNumber;
            }
        }
        
        // If not from PO, it's a manual addition
        return 'Manual Addition';
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
    
    private function calculateStats($timePeriod)
    {
        // Calculate total stock in (actual inventory additions)
        $totalStockIn = Inventory::sum('productStock');

        // Calculate total stock out (from sales)
        $totalStockOut = SaleItem::sum('quantity');
        
        // Calculate revenue and cost
        $totalRevenue = SaleItem::sum(DB::raw('quantity * unit_price'));
        $totalCost = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
            ->sum(DB::raw('sale_items.quantity * inventories.productCostPrice'));
        $totalProfit = $totalRevenue - $totalCost;

        return compact('totalStockIn', 'totalStockOut', 'totalRevenue', 'totalCost', 'totalProfit');
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