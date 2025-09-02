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
        Schema::table('bad_items', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bad_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropColumn('purchase_order_item_id');
        });
    }
};