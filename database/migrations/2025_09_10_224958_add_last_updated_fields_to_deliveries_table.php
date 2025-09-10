<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('last_updated_by')->default('System')->after('orderStatus');
            $table->timestamp('status_updated_at')->nullable()->after('last_updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['last_updated_by', 'status_updated_at']);
        });
    }
};