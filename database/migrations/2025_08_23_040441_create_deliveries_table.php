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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('deliveryId');
            $table->enum('orderStatus', ['Pending', 'Delivered', 'Cancelled', 'Confirmed'])->default('Pending');
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->index('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
