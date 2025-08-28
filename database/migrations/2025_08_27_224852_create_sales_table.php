<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Unique invoice ID
            $table->date('sale_date'); // Date of sale
            $table->string('customer_name'); // Customer name
            $table->decimal('total_amount', 10, 2); // Total sale amount
            $table->decimal('cash_received', 10, 2); // Cash received from customer
            $table->decimal('change', 10, 2); // Change given back
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->integer('quantity'); // Quantity sold
            $table->decimal('unit_price', 10, 2); // Price per unit at time of sale
            $table->decimal('total_price', 10, 2); // quantity * unit_price
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
