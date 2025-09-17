<?php
// database/migrations/2024_05_20_000001_drop_customers_table.php
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
        Schema::dropIfExists('customers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the customers table schema based on the old model
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customerName');
            $table->text('customerAddress')->nullable();
            $table->string('customerContactNumber')->nullable();
            $table->string('customerEmailAddress')->nullable();
            $table->string('customerStatus')->default('active');
            $table->timestamps();
        });
    }
};