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
        Schema::create('bad_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade'); // link to product
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null'); // if it came from a PO
            $table->string('quality_status'); // e.g. "Defective", "Wrong Label", "Expired"
            $table->integer('item_count'); // number of bad items
            $table->text('notes')->nullable(); // user comments
            $table->enum('status', ['Pending', 'Reviewed', 'Reported', 'Resolved'])->default('Pending'); // workflow
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bad_items');
    }
};
