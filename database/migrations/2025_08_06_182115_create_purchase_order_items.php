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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('productName');
            $table->integer('quantity');
            $table->decimal('unitPrice', 8, 2);
            $table->decimal('totalAmount', 10, 2);
            $table->enum('itemMeasurement', ['kilogram', 'gram', 'liter', 'milliliter', 'pcs', 'set', 'pair', 'pack']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
