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
        Schema::table('sale_items', function (Blueprint $table) {
            // Add new columns
            $table->foreignId('product_id')->nullable()->after('inventory_id');
            $table->foreignId('product_batch_id')->nullable()->after('product_id');
            
            // Add foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_batch_id')->references('id')->on('product_batches')->onDelete('cascade');
            
            // Keep inventory_id for backward compatibility, but make it nullable
            $table->foreignId('inventory_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
