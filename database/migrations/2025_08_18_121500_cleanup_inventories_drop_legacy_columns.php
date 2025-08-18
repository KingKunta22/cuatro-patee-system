<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill new columns from legacy columns if needed, then drop legacy ones
        if (Schema::hasTable('inventories')) {
            // Copy values only where the new columns are NULL and legacy columns exist
            if (Schema::hasColumn('inventories', 'productSellingPrice') && Schema::hasColumn('inventories', 'sellingPrice')) {
                DB::statement('UPDATE inventories SET productSellingPrice = sellingPrice WHERE productSellingPrice IS NULL');
            }
            if (Schema::hasColumn('inventories', 'productCostPrice') && Schema::hasColumn('inventories', 'costPrice')) {
                DB::statement('UPDATE inventories SET productCostPrice = costPrice WHERE productCostPrice IS NULL');
            }
            if (Schema::hasColumn('inventories', 'productProfitMargin') && Schema::hasColumn('inventories', 'profitMargin')) {
                DB::statement('UPDATE inventories SET productProfitMargin = profitMargin WHERE productProfitMargin IS NULL');
            }
            if (Schema::hasColumn('inventories', 'productImage') && Schema::hasColumn('inventories', 'image')) {
                DB::statement('UPDATE inventories SET productImage = image WHERE productImage IS NULL');
            }

            Schema::table('inventories', function (Blueprint $table) {
                $columnsToDrop = [];
                foreach (['sellingPrice', 'costPrice', 'profitMargin', 'image'] as $legacyColumn) {
                    if (Schema::hasColumn('inventories', $legacyColumn)) {
                        $columnsToDrop[] = $legacyColumn;
                    }
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventories')) {
            Schema::table('inventories', function (Blueprint $table) {
                if (!Schema::hasColumn('inventories', 'sellingPrice')) {
                    $table->decimal('sellingPrice', 8, 2)->nullable();
                }
                if (!Schema::hasColumn('inventories', 'costPrice')) {
                    $table->decimal('costPrice', 8, 2)->nullable();
                }
                if (!Schema::hasColumn('inventories', 'profitMargin')) {
                    $table->decimal('profitMargin', 5, 2)->nullable();
                }
                if (!Schema::hasColumn('inventories', 'image')) {
                    $table->string('image')->nullable();
                }
            });

            // Backfill legacy columns from new columns
            if (Schema::hasColumn('inventories', 'productSellingPrice') && Schema::hasColumn('inventories', 'sellingPrice')) {
                DB::statement('UPDATE inventories SET sellingPrice = productSellingPrice WHERE sellingPrice IS NULL');
            }
            if (Schema::hasColumn('inventories', 'productCostPrice') && Schema::hasColumn('inventories', 'costPrice')) {
                DB::statement('UPDATE inventories SET costPrice = productCostPrice WHERE costPrice IS NULL');
            }
            if (Schema::hasColumn('inventories', 'productProfitMargin') && Schema::hasColumn('inventories', 'profitMargin')) {
                DB::statement('UPDATE inventories SET profitMargin = productProfitMargin WHERE profitMargin IS NULL');
            }
            if (Schema::hasColumn('inventories', 'productImage') && Schema::hasColumn('inventories', 'image')) {
                DB::statement('UPDATE inventories SET image = productImage WHERE image IS NULL');
            }
        }
    }
};


