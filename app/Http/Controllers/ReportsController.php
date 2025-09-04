<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class ReportsController extends Controller
{
    public function index()
    {
        // Get all delivered POs with their items and bad items for the PO reports tab
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items', 'items.inventory', 'items.badItems'])
            ->whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        $inventories = Inventory::with(['category', 'brand'])
                        ->paginate(10);
        
        return view('reports', compact('purchaseOrders', 'inventories'));
    }

    public function purchaseOrderReports()
    {
        // Get all delivered POs with their items and bad items
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items', 'items.inventory', 'items.badItems'])
            ->whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        
        return view('reports.purchase-order-reports', compact('purchaseOrders'));
    }

    public function updateDefectiveStatus(Request $request, PurchaseOrderItem $item)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Reviewed,Reported,Resolved',
            'notes' => 'nullable|string',
        ]);

        // Update all bad items for this purchase order item
        $item->badItems()->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?: $item->badItems->first()->notes
        ]);

        return redirect()->back()->with('success', 'Defective items status updated successfully!');
    }
}


