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

        // Ensure all purchase orders have delivery records
        $ordersWithoutDelivery = PurchaseOrder::doesntHave('deliveries')->get();
        foreach ($ordersWithoutDelivery as $order) {
            Delivery::create([
                'purchase_order_id' => $order->id,
                'deliveryId' => Delivery::generateDeliveryId(),
                'orderStatus' => 'Pending'
            ]);
        }

        
        // Apply status filter
        if ($status !== 'all') {
            if ($status === 'Delayed') {
                // Filter for delayed orders (Confirmed status + past delivery date)
                $query->whereHas('deliveries', function($q) {
                    $q->where('orderStatus', 'Confirmed')
                    ->whereDate('deliveryDate', '<', now()->toDateString());
                });
            } else {
                // FIXED: For Pending, Confirmed, Delivered, Cancelled - use proper filtering
                if ($status === 'Pending') {
                    // For Pending: include orders with Pending status OR no delivery records
                    $query->where(function($q) {
                        $q->whereHas('deliveries', function($q) {
                            $q->where('orderStatus', 'Pending');
                        })->orDoesntHave('deliveries');
                    });
                } else {
                    // For Confirmed, Delivered, Cancelled: only show exact status matches
                    $query->whereHas('deliveries', function($q) use ($status) {
                        $q->where('orderStatus', $status);
                    });
                }
            }
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
            
            // Get the delivery record
            $delivery = $purchaseOrder->deliveries->first();
            
            if (!$delivery) {
                // Fallback: create delivery if it doesn't exist
                $delivery = $purchaseOrder->deliveries()->create([
                    'deliveryId' => Delivery::generateDeliveryId(),
                    'orderStatus' => 'Pending'
                ]);
            }
            
            // Prevent status changes if already Delivered or Cancelled
            if (in_array($delivery->orderStatus, ['Delivered', 'Cancelled'])) {
                return redirect()->back()->with('error', 'Cannot change status for delivered or cancelled orders.');
            }
            
            // Update the status
            $delivery->update([
                'orderStatus' => $request->status,
                'status_updated_at' => now(),
                'last_updated_by' => auth()->user()->name ?? 'System'
            ]);
            
            return redirect()->back()->with('success', 'Delivery status updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }

}