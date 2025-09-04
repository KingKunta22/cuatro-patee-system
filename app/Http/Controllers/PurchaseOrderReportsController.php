<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;

class PurchaseOrderReportsController extends Controller
{
    public function index()
    {
        // Logic moved from ReportsController::index and purchaseOrderReports
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items', 'items.inventory', 'items.badItems'])
            ->whereHas('deliveries', function($query) {
                $query->where('orderStatus', 'Delivered');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        // Return the view for the PO reports tab content
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