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
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Start with base query
        $query = PurchaseOrder::with(['items', 'supplier', 'deliveries']);

        // Apply status filter (your existing code)
        if ($status !== 'all') {
            if ($status === 'Delayed') {
                $query->whereHas('deliveries', function($q) {
                    $q->where('orderStatus', 'Confirmed')
                    ->whereDate('deliveryDate', '<', now()->toDateString());
                });
            } else {
                if ($status === 'Pending') {
                    $query->where(function($q) {
                        $q->whereHas('deliveries', function($q) {
                            $q->where('orderStatus', 'Pending');
                        })->orDoesntHave('deliveries');
                    });
                } else {
                    $query->whereHas('deliveries', function($q) use ($status) {
                        $q->where('orderStatus', $status);
                    });
                }
            }
        }

        // Apply sorting
        switch ($sortBy) {
            case 'delivery_id':
                $query->join('deliveries', 'purchase_orders.id', '=', 'deliveries.purchase_order_id')
                    ->orderBy('deliveries.deliveryId', $sortOrder)
                    ->select('purchase_orders.*');
                break;
            case 'order_date':
                $query->orderBy('purchase_orders.created_at', $sortOrder);
                break;
            case 'expected_date':
                $query->orderBy('purchase_orders.deliveryDate', $sortOrder);
                break;
            case 'lead_time':
                // For lead time, we need to calculate it
                $query->orderBy('purchase_orders.deliveryDate', $sortOrder === 'asc' ? 'desc' : 'asc');
                break;
            case 'status':
                $query->join('deliveries', 'purchase_orders.id', '=', 'deliveries.purchase_order_id')
                    ->orderBy('deliveries.orderStatus', $sortOrder)
                    ->select('purchase_orders.*');
                break;
            default:
                $query->orderBy('purchase_orders.id', $sortOrder);
        }

        // Apply search filter (your existing code)
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
                ->orWhere('orderNumber', 'LIKE', "%{$searchTerm}%");
            });
        }

        $suppliers = Supplier::where('supplierStatus', 'Active')->get();

        // Order and paginate
        $purchaseOrder = $query->orderBy('purchase_orders.id', 'DESC')
            ->paginate(7)
            ->withQueryString();
        
        return view('delivery-management', compact('purchaseOrder', 'suppliers', 'sortBy', 'sortOrder'));
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