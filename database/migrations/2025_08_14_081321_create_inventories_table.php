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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('productName');
            $table->string('productBrand');
            $table->string('productCategory');
            $table->decimal('sellingPrice', 8, 2);
            $table->decimal('costPrice', 8, 2);
            $table->decimal('profitMargin', 5, 2);
            $table->string('image')->nullable(); // Stores the image path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
