<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;

class PurchaseOrderReportsController extends Controller
{
    public function index(Request $request)
    {
        // Start with base query - get POs that have delivered batches
        $query = PurchaseOrder::with([
            'supplier', 
            'items', 
            'items.productBatch', // Changed from items.inventory
            'items.badItems', 
            'deliveries'
        ])
        ->whereHas('items.productBatch', function($q) {
            $q->where('quantity', '>', 0); // Only POs with actual stock
        });

        // Apply time period filter
        $timePeriod = $request->timePeriod ?? 'all';

        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'lastWeek':
                    $query->whereBetween('created_at', [now()->subDays(7), now()]);
                    break;
                case 'lastMonth':
                    $query->whereBetween('created_at', [now()->subDays(30), now()]);
                    break;
            }
        }

        // Order by delivery status update time (most recent delivered first)
        $purchaseOrders = $query->orderByDesc(function($query) {
                $query->select('status_updated_at')
                    ->from('deliveries')
                    ->whereColumn('purchase_orders.id', 'deliveries.purchase_order_id')
                    ->where('orderStatus', 'Delivered')
                    ->orderBy('status_updated_at', 'desc')
                    ->limit(1);
            })
            ->paginate(10, ['*'], 'po_page');

        return view('reports.purchase-order-reports', compact('purchaseOrders', 'timePeriod'));
    }

    public function updateDefectiveStatus(Request $request, PurchaseOrderItem $item)
    {
        // Keep the update logic here
        $validated = $request->validate([
            'status' => 'required|in:Pending,Reviewed,Reported,Resolved',
            'notes' => 'nullable|string',
        ]);

        $item->badItems()->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?: $item->badItems->first()->notes
        ]);

        return redirect()->back()->with('success', 'Defective items status updated successfully!');
    }

public function print(Request $request)
{
    // Start with base query - get POs that have delivered batches
    $query = PurchaseOrder::with([
        'supplier', 
        'items', 
        'items.productBatches', // ← FIXED: Changed from productBatch to productBatches
        'items.badItems', 
        'deliveries',
        'notes'
    ])
    ->whereHas('items.productBatches', function($q) { // ← FIXED: Changed from productBatch to productBatches
        $q->where('quantity', '>', 0);
    });

    // Apply time period filter
    $timePeriod = $request->timePeriod ?? 'all';

    if ($timePeriod !== 'all') {
        switch ($timePeriod) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'lastWeek':
                $query->whereBetween('created_at', [now()->subDays(7), now()]);
                break;
            case 'lastMonth':
                $query->whereBetween('created_at', [now()->subDays(30), now()]);
                break;
        }
    }

    $purchaseOrders = $query->orderByDesc(function($query) {
            $query->select('status_updated_at')
                ->from('deliveries')
                ->whereColumn('purchase_orders.id', 'deliveries.purchase_order_id')
                ->where('orderStatus', 'Delivered')
                ->orderBy('status_updated_at', 'desc')
                ->limit(1);
        })
        ->get();

    return view('reports.print.purchase-order-print', compact('purchaseOrders', 'timePeriod'));
}
}