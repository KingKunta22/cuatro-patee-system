<?php

use App\Models\Delivery;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // First, check if purchase_orders table has orderStatus column
        if (Schema::hasColumn('purchase_orders', 'orderStatus')) {
            // Add a temporary column to purchase_orders to store the old status
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->enum('old_order_status', ['Pending', 'Delivered', 'Cancelled', 'Confirmed'])->nullable();
            });

            // Copy the existing orderStatus to the temporary column
            DB::statement('UPDATE purchase_orders SET old_order_status = orderStatus');

            // Remove the orderStatus column from purchase_orders
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('orderStatus');
            });

            // Create deliveries for each purchase order
            $purchaseOrders = PurchaseOrder::all();
            
            foreach ($purchaseOrders as $po) {
                Delivery::create([
                    'deliveryId' => 'DEL-' . $po->orderNumber . '-' . time(),
                    'orderStatus' => $po->old_order_status,
                    'purchase_order_id' => $po->id
                ]);
            }

            // Remove the temporary column
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('old_order_status');
            });
        }
    }

    public function down(): void
    {
        // This would be more complex to reverse
        // You might want to handle this manually if needed
    }
};