<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    public function index() {
        $supplierNames = Supplier::select('id', 'supplierName')->where('supplierStatus', 'Active')->get();
        return view('purchase-orders', compact('supplierNames'));
    }

    // ADD ITEM TO SESSION (temporary storage)
    public function addItem(Request $request) {
        $validated = $request->validate([
            'supplierId' => 'required|exists:suppliers,id',
            'productName' => 'required|string',
            'paymentTerms' => 'required|in:Online,Cash on Delivery',
            'unitPrice' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'deliveryDate' => 'required|date',
        ]);

        // Calculate total amount
        $validated['totalAmount'] = $validated['unitPrice'] * $validated['quantity'];

        // Add to session
        $items = session('purchase_order_items', []);
        $items[] = $validated;
        session(['purchase_order_items' => $items]);

        return back()->with('success', 'Item added to order');
    }

    // SAVE ALL ITEMS TO DATABASE
    public function store(Request $request) {
        $items = session('purchase_order_items', []);
        
        if (empty($items)) {
            return back();
        }

        // Generate order number once for all items
        $orderNumber = $this->generateOrderNumber();

        // Save each item to database
        foreach ($items as $item) {
            PurchaseOrder::create([
                'orderNumber' => $orderNumber,          // Generated automatically
                'supplierId' => $item['supplierId'],    // From session
                'productName' => $item['productName'],
                'paymentTerms' => $item['paymentTerms'],
                'unitPrice' => $item['unitPrice'],
                'quantity' => $item['quantity'],
                'deliveryDate' => $item['deliveryDate'],
                'totalAmount' => $item['totalAmount'],  // Calculated in addItem
                'orderStatus' => 'Pending'              // Default value
            ]);
        }

        // Clear session after saving
        session()->forget('purchase_order_items');

        return redirect()->route('purchase-orders.index');
    }

    private function generateOrderNumber() {
        $year = date('Y');
        $lastOrder = PurchaseOrder::where('orderNumber', 'like', "PO-{$year}-%")
                                  ->orderBy('orderNumber', 'desc')
                                  ->first();
        
        if (!$lastOrder) {
            $nextNumber = 1;
        } else {
            $lastNumber = (int) substr($lastOrder->orderNumber, -4);
            $nextNumber = $lastNumber + 1;
        }
        
        return "PO-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}