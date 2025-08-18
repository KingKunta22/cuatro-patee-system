<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->unique('productSKU');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Default index name convention: table_column_unique
            $table->dropUnique('inventories_productsku_unique');
        });
    }
};


