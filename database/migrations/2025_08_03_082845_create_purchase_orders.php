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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('orderNumber')->unique();
            $table->foreignId('supplierId')->constrained('suppliers');
            $table->enum('paymentTerms', ['Online', 'Cash on Delivery']);
            $table->date('deliveryDate');
            $table->decimal('totalAmount', 10, 2);
            $table->enum('orderStatus', ['Pending', 'Delivered', 'Cancelled', 'Confirmed'])->default('Pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
