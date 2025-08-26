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
        $query = PurchaseOrder::with(['items', 'supplier', 'deliveries']);

        // Apply status filter
        if ($status !== 'all') {
            $query->whereHas('deliveries', function($q) use ($status) {
                $q->where('orderStatus', $status);
            });
        }

        // Apply search filter - UPDATED to search deliveryId properly
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('deliveries', function($q) use ($searchTerm) {
                    $q->where('deliveryId', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('supplier', function($q) use ($searchTerm) {
                    $q->where('supplierName', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('items', function($q) use ($searchTerm) {
                    $q->where('productName', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhere('orderNumber', 'LIKE', "%{$searchTerm}%"); // Also search by order number
            });
        }

        $suppliers = Supplier::where('supplierStatus', 'Active')->get();

        // Order and paginate
        $purchaseOrder = $query->orderBy('id', 'DESC')
            ->paginate(7)
            ->withQueryString();
        
        return view('delivery-management', compact('purchaseOrder', 'suppliers'));
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|in:Pending,Confirmed,Delivered,Cancelled'
            ]);

            $purchaseOrder = PurchaseOrder::findOrFail($request->order_id);
            
            // Get the delivery record (should already exist due to model event)
            $delivery = $purchaseOrder->deliveries->first();
            
            if (!$delivery) {
                // Fallback: create delivery if it doesn't exist (for existing orders)
                $delivery = $purchaseOrder->deliveries()->create([
                    'deliveryId' => Delivery::generateDeliveryId(),
                    'orderStatus' => 'Pending'
                ]);
            }
            
            // Update the status
            $delivery->update(['orderStatus' => $request->status]);
            
            return redirect()->back()->with('success', 'Delivery status updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }
}