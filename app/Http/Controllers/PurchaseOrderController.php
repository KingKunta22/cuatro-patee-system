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

    public function store(Request $request){
        // Gather added items
        $inputtedOrders = $request->validate([
            'supplierId'=>'required',
            'productName'=>'required',
            'paymentTerms'=>'required|in:Online,Cash on Delivery',
            'unitPrice'=>'required',
            'quantity'=>'required',
            'deliveryDate'=>'required',
        ]);

        /* $inputtedOrders['orderNumber'] = 
        $inputtedOrders['supplierId'] = 
        $inputtedOrders['totalAmount'] =  */
        // 'orderNumber'=>'required',
        // 'supplierId'
        // getTotal
    }

    // Inside PurchaseOrderController
    private function generateOrderNumber()
    {
        // Get current year
        $year = date('Y');
        
        // Find the highest order number for current year
        $lastOrder = PurchaseOrder::where('orderNumber', 'like', "PO-{$year}-%")
                                ->orderBy('orderNumber', 'desc')
                                ->first();
        
        // If no orders exist for this year, start at 1
        if (!$lastOrder) {
            $nextNumber = 1;
        } else {
            // Extract number from "PO-2025-0001" format
            $lastNumber = (int) substr($lastOrder->orderNumber, -4);
            $nextNumber = $lastNumber + 1;
        }
        
        // Format: PO-2025-0001
        return "PO-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
