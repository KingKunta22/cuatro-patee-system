<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;

class PurchaseOrderReportsController extends Controller
{
    public function index(Request $request)
    {
        // Start with base query
        $query = PurchaseOrder::with(['supplier', 'items', 'items.inventory', 'items.badItems', 'deliveries'])
            ->whereHas('deliveries', function($q) {
                $q->where('orderStatus', 'Delivered');
            });

        // Apply time period filter
        $timePeriod = $request->timePeriod ?? 'all';

        if ($timePeriod !== 'all') {
            switch ($timePeriod) {
                case 'daily':
                    $query->whereDate('created_at', today());
                    break;
                case 'weekly':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'monthly':
                    $query->whereMonth('created_at', now()->month)
                         ->whereYear('created_at', now()->year);
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
            ->paginate(10);

        return view('reports.purchase-order-reports', compact('purchaseOrders'));
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
}