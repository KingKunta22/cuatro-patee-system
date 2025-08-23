<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        // Start with base query
        $query = PurchaseOrder::with(['items', 'supplier', 'deliveries']); // Add 'deliveries' to with()

        // Apply status filter - UPDATED
        if ($status !== 'all') {
            $query->whereHas('deliveries', function($q) use ($status) {
                $q->where('orderStatus', $status);
            });
        }

        // Apply search filter
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('deliveryId', 'LIKE', "%{$searchTerm}%")
                ->orWhereHas('supplier', function($q) use ($searchTerm) {
                    $q->where('supplierName', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('items', function($q) use ($searchTerm) {
                    $q->where('productName', 'LIKE', "%{$searchTerm}%");
                });
            });
        }

        $suppliers = Supplier::where('supplierStatus', 'Active')
                        ->get();

        // Order and paginate
        $purchaseOrder = $query->orderBy('id', 'DESC')
            ->paginate(7)
            ->withQueryString();


        foreach ($purchaseOrder as $po) {
            if ($po->deliveries->count() > 0) {
                $status = $po->deliveries->first()->orderStatus;
                // Do something with the status
            }
        }
        
        return view('delivery-management', compact('purchaseOrder', 'suppliers'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:purchase_orders,id',
            'status' => 'required|in:Pending,Confirmed,Delivered,Cancelled'
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($request->order_id);
        
        // Find or create delivery record
        $delivery = \App\Models\Delivery::firstOrCreate(
            ['purchase_order_id' => $request->order_id],
            [
                'deliveryId' => \App\Models\Delivery::generateDeliveryId(),
                'orderStatus' => 'Pending'
            ]
        );
        
        // Update the status
        $delivery->update(['orderStatus' => $request->status]);
        
        return redirect()->back()->with('success', 'Delivery status updated successfully!');
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:purchase_orders,id',
                'status' => 'required|in:Pending,Confirmed,Delivered,Cancelled'
            ]);

            $purchaseOrder = PurchaseOrder::findOrFail($request->order_id);
            
            // Find or create delivery record
            $delivery = Delivery::firstOrCreate(
                ['purchase_order_id' => $request->order_id],
                [
                    'deliveryId' => Delivery::generateDeliveryId(),
                    'orderStatus' => 'Pending'
                ]
            );
            
            // Update the status
            $delivery->update(['orderStatus' => $request->status]);
            
            return redirect()->back()->with('success', 'Delivery status updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }
}
