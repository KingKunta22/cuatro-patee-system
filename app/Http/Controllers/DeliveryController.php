<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        // Start with base query
        $query = PurchaseOrder::with(['items', 'supplier']);

        // Apply status filter
        if ($status !== 'all') {
            $query->where('orderStatus', $status);
        }

        // Apply search filter - ADD TO EXISTING QUERY
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('orderNumber', 'LIKE', "%{$searchTerm}%")
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
        
        return view('delivery-management', compact('purchaseOrder', 'suppliers'));
    }
}
