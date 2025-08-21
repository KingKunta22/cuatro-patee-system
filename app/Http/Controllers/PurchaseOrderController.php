<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Mail\PurchaseOrderMail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Mail;


class PurchaseOrderController extends Controller
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

        // Order and paginate
        $purchaseOrders = $query->orderBy('id', 'DESC')
            ->paginate(6)
            ->withQueryString();

        $supplierNames = Supplier::where('supplierStatus', 'Active')->get();
        $items = session('purchase_order_items', []);
        $lockedSupplierId = !empty($items) ? $items[0]['supplierId'] : null;

        return view('purchase-orders', compact(
            'supplierNames',
            'lockedSupplierId',
            'items',
            'purchaseOrders'
        ));
    }


    // ADD ITEM TO SESSION (temporary storage)
    public function addItem(Request $request) 
    {
        $validated = $request->validate([
            'supplierId' => 'required|exists:suppliers,id',
            'productName' => 'required|string',
            'paymentTerms' => 'required|in:Online,Cash on Delivery',
            'unitPrice' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'deliveryDate' => 'required|date|after_or_equal:today',
            'itemMeasurement' => 'required|in:kilogram,gram,liter,milliliter,pcs,set,pair,pack',
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
    public function store(Request $request) 
    {
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
                'itemMeasurement' => $item['itemMeasurement'],
                'totalAmount' => $item['totalAmount'],
            ]);
        }


        session()->forget('purchase_order_items');

        // Check PDF checkbox from FINAL form submission
        if ($request->has('savePDF')) {
            // Store the order ID in session for PDF download
            session(['download_pdf' => $order->id]);
        }


        // If "sendEmail" checkbox is checked
        if ($request->has('sendEmail')) {
            $orderData = PurchaseOrder::with(['items', 'supplier'])->findOrFail($order->id);

            // Generate PDF from your blade view
            $pdf = Pdf::loadView('InvoicePDF', ['order' => $orderData]);

            // Send email with attachment
            Mail::to($orderData->supplier->supplierEmailAddress) // Sends to supplier's supplierEmailAddress column
                ->send(new PurchaseOrderMail($orderData, $pdf->output()));
        }


        // After saving to database
        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order saved successfully!');
    }


    
    // GENERATE PDF 
    public function generatePDF($orderId = null)
    {
        if ($orderId) {
            // Get the actual purchase order data
            $order = PurchaseOrder::with(['items', 'supplier'])->findOrFail($orderId);
            
            $data = [
                'title' => 'Purchase Order',
                'orderNumber' => $order->orderNumber,
                'date' => $order->created_at->format('m-d-Y'),
                'supplier' => $order->supplier,
                'paymentTerms' => $order->paymentTerms,
                'deliveryDate' => $order->deliveryDate,
                'items' => $order->items,
                'totalAmount' => $order->totalAmount,
                'orderStatus' => $order->orderStatus
            ];
            
            $filename = $order->orderNumber . '.pdf';
        } else {
            // Fallback demo data
            $data = [
                'title' => 'This is a demo',
                'date' => date('m-d-Y'),
                'content' => 'Lorem Lorem Lorem'
            ];
            
            $filename = 'generatePDFTest.pdf';
        }

        $pdf = Pdf::loadView('InvoicePDF', $data);
        return $pdf->download($filename);
    }

    // DOWNLOAD PDF AFTER SAVING ORDER
    public function downloadPDF($orderId)
    {
        // Clear the download_pdf session
        session()->forget('download_pdf');
        
        return $this->generatePDF($orderId);
    }


    // GENERATE ORDER NUMBER WITH THIS FORMAT: PO-2025-0001
    private function generateOrderNumber() 
    {
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



    // Removes an item inside the session that exists
    public function removeItem($index) 
    {
        $items = session('purchase_order_items', []);
        
        if (isset($items[$index])) {
            unset($items[$index]);
            $items = array_values($items); // Re-index array
            session(['purchase_order_items' => $items]);
            
            return redirect()->back()->with('keep_modal_open', true);
        }
        
        return back();
    }



    // CLEAR SESSION | CLEARS THE TEMPORARY ADDED ITEMS
    public function clearSession()
    {
        session()->forget('purchase_order_items');
        return back();
    }


    // DELETE PURCHASE ORDER
    public function destroy(PurchaseOrder $purchaseOrder){
        $purchaseOrder->delete();

        return redirect()->back();
    }


    // DELETE PURCHASE ORDER ITEM 
    public function destroyItem($purchaseOrderId, $itemId)
    {
        $item = PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)->where('id', $itemId)->firstOrFail();

        $item->delete();

        return redirect()->back();
    }


    // UPDATE EVERYTHING
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {

        // Handle item removal
        if ($request->has('remove_item')) {
            $itemId = $request->input('remove_item');
            $item = PurchaseOrderItem::findOrFail($itemId);
            $item->delete();
            
            return redirect()->back();
        }


        // Validate the request
        $validated = $request->validate([
            'paymentTerms' => 'required|in:Online,Cash on Delivery',
            'orderStatus' => 'required|in:Pending,Delivered,Cancelled,Confirmed',
            'deliveryDate' => 'required|date',
            'items' => 'required|array',
            'items.*.productName' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unitPrice' => 'required|numeric|min:0',
            'items.*.itemMeasurement' => 'required|in:kilogram,gram,liter,milliliter,pcs,set,pair,pack',
        ]);


        // Update the main order
        $purchaseOrder->update([
            'paymentTerms' => $validated['paymentTerms'],
            'orderStatus' => $validated['orderStatus'],
            'deliveryDate' => $validated['deliveryDate']
        ]);

        $totalAmount = 0;

        // Update items
        foreach ($validated['items'] as $itemId => $itemData) {
            $item = PurchaseOrderItem::findOrFail($itemId);
            $item->update([
                'productName' => $itemData['productName'],
                'quantity' => $itemData['quantity'],
                'unitPrice' => $itemData['unitPrice'],
                'itemMeasurement' => $itemData['itemMeasurement'],
                'totalAmount' => $itemData['quantity'] * $itemData['unitPrice']
            ]);
            $totalAmount += $item->totalAmount;
        }

        // Update total amount
        $purchaseOrder->update(['totalAmount' => $totalAmount]);

        return redirect()->route('purchase-orders.index');
    }
}