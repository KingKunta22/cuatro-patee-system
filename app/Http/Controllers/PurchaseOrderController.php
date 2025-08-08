<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class PurchaseOrderController extends Controller
{
public function index() {
    $supplierNames = Supplier::where('supplierStatus', 'Active')->get();
    $items = session('purchase_order_items', []);
    $lockedSupplierId = $items[0]['supplierId'] ?? null;
    $purchaseOrders = PurchaseOrder::with(['items', 'supplier'])->get();
    
    return view('purchase-orders', compact('supplierNames', 'lockedSupplierId', 'items', 'purchaseOrders'));
}

    // ADD ITEM TO SESSION (temporary storage)
    public function addItem(Request $request) {
        $validated = $request->validate([
            'supplierId' => 'required|exists:suppliers,id',
            'productName' => 'required|string',
            'paymentTerms' => 'required|in:Online,COD',
            'unitPrice' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'deliveryDate' => 'required|date',
        ]);

        // Calculate total amount
        $validated['totalAmount'] = $validated['unitPrice'] * $validated['quantity'];

        // Add to session
        $items = session('purchase_order_items', []);

        // Supplier lock validation
        if (!empty($items)) {
            $request->validate([
                'supplierId' => 'required|in:'.$items[0]['supplierId']
            ]);
        }

        $items[] = $validated;
        session(['purchase_order_items' => $items]);

        return redirect()->back()->with('keep_modal_open', true);
    }



    // SAVE ALL ITEMS TO DATABASE
    public function store(Request $request) {
        $items = session('purchase_order_items', []);

        if (empty($items)) {
            return back();
        }

        $firstItem = $items[0];

        // Create the purchase order (1 row)
        $order = PurchaseOrder::create([
            'orderNumber' => $this->generateOrderNumber(),
            'supplierId' => $firstItem['supplierId'],
            'paymentTerms' => $firstItem['paymentTerms'],
            'deliveryDate' => $firstItem['deliveryDate'],
            'totalAmount' => collect($items)->sum('totalAmount'),
            'orderStatus' => 'Pending'
        ]);

        // Create purchase order items (many rows)
        foreach ($items as $item) {
            $order->items()->create([
                'productName' => $item['productName'],
                'quantity' => $item['quantity'],
                'unitPrice' => $item['unitPrice'],
                'totalAmount' => $item['totalAmount'],
            ]);
        }

        session()->forget('purchase_order_items');

        return redirect()->route('purchase-orders.index');
    }



    private function generateOrderNumber() {
        $year = date('Y');
        $lastOrder = PurchaseOrder::where('orderNumber', 'like', "PO-{$year}-%")->orderBy('orderNumber', 'desc')->first();
        
        if (!$lastOrder) {
            $nextNumber = 1;
        } else {
            $lastNumber = (int) substr($lastOrder->orderNumber, -4);
            $nextNumber = $lastNumber + 1;
        }
        
        // Format: PO-2025-0001
        return "PO-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function removeItem($index) {
        $items = session('purchase_order_items', []);
        
        if (isset($items[$index])) {
            unset($items[$index]);
            $items = array_values($items); // Re-index array
            session(['purchase_order_items' => $items]);
            
            return redirect()->back()->with('keep_modal_open', true);
        }
        
        return back();
    }



    // Clear session or clears the temporary added items
    public function clearSession(){
        session()->forget('purchase_order_items');
        return back();
    }

    public function destroy(PurchaseOrder $purchaseOrder){
        $purchaseOrder->delete();

        return redirect()->back();
    }


    public function updatePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder)
    {


    }

}