<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\PurchaseOrder;

class NotificationController extends Controller
{
    public function checkNotifications()
    {
        $notifications = [];
        
        // 1. Check low stock (stock <= 10)
        $lowStockProducts = Product::with('batches')->get();
        
        foreach ($lowStockProducts as $product) {
            $totalStock = $product->batches->sum('quantity');
            if ($totalStock <= 10) {
                $notifications[] = [
                    'id' => 'low_stock_' . $product->id,
                    'title' => 'Low Stock Alert',
                    'message' => $product->productName . ' is running low! Stock: ' . $totalStock,
                    'url' => '/inventory',
                    'time' => 'Today',
                    'created_at' => now()->timestamp
                ];
            }
        }
        
        // 2. Check expiring products (within 30 days)
        $expiringBatches = ProductBatch::where('expiration_date', '<=', now()->addDays(30))
            ->where('expiration_date', '>', now())
            ->where('quantity', '>', 0)
            ->with('product')
            ->get();
        
        foreach ($expiringBatches as $batch) {
            $days = floor(now()->diffInDays($batch->expiration_date));
            $notifications[] = [
                'id' => 'expiring_' . $batch->id,
                'title' => 'Product Expiring Soon',
                'message' => $batch->product->productName . " expires in {$days} days",
                'url' => '/inventory',
                'time' => 'Today',
                'created_at' => now()->timestamp
            ];
        }
        
        // 3. Check for delivered orders that aren't in inventory
        $deliveredOrders = PurchaseOrder::whereHas('deliveries', function($query) {
            $query->where('orderStatus', 'Delivered');
        })->with(['items', 'deliveries'])->get();
        
        foreach ($deliveredOrders as $order) {
            $hasUnaddedItems = false;
            
            foreach ($order->items as $item) {
                $batchExists = ProductBatch::where('purchase_order_id', $order->id)
                    ->where('purchase_order_item_id', $item->id)
                    ->exists();
                    
                if (!$batchExists) {
                    $hasUnaddedItems = true;
                    break;
                }
            }
            
            if ($hasUnaddedItems) {
                $notifications[] = [
                    'id' => 'delivered_' . $order->id,
                    'title' => 'Delivery Ready for Inventory',
                    'message' => "Order #{$order->id} has been delivered - add items to inventory",
                    'url' => '/inventory?add_delivery=' . $order->id,
                    'time' => 'Today',
                    'created_at' => $order->deliveries->first()->status_updated_at?->timestamp ?? now()->timestamp
                ];
            }
        }
        
        // Order by newest to latest (most recent first)
        usort($notifications, function($a, $b) {
            return ($b['created_at'] ?? 0) <=> ($a['created_at'] ?? 0);
        });
        
        return response()->json(['notifications' => $notifications]);
    }
}