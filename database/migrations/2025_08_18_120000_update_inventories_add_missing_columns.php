<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Core identifying fields
            $table->string('productSKU')->after('productName');

            // Inventory and pricing fields used by the app code
            $table->integer('productStock')->default(0)->after('productCategory');
            $table->decimal('productSellingPrice', 10, 2)->after('productStock');
            $table->decimal('productCostPrice', 10, 2)->after('productSellingPrice');
            $table->decimal('productProfitMargin', 10, 2)->after('productCostPrice');

            // Item details
            $table->string('productItemMeasurement')->after('productProfitMargin');
            $table->date('productExpirationDate')->after('productItemMeasurement');
            $table->string('productImage')->nullable()->after('productExpirationDate');

            // Optional references back to PO and PO item
            $table->unsignedBigInteger('purchase_order_id')->nullable()->after('productImage');
            $table->unsignedBigInteger('purchase_order_item_id')->nullable()->after('purchase_order_id');

            $table->foreign('purchase_order_id')
                ->references('id')->on('purchase_orders')
                ->nullOnDelete();

            $table->foreign('purchase_order_item_id')
                ->references('id')->on('purchase_order_items')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Drop FKs first then columns
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['purchase_order_item_id']);

            $table->dropColumn([
                'productSKU',
                'productStock',
                'productSellingPrice',
                'productCostPrice',
                'productProfitMargin',
                'productItemMeasurement',
                'productExpirationDate',
                'productImage',
                'purchase_order_id',
                'purchase_order_item_id',
            ]);
        });
    }
};


