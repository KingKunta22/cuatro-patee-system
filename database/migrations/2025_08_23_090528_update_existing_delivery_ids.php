<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Delivery;

return new class extends Migration
{
    public function up(): void
    {
        // Check if there are any deliveries to update
        if (Delivery::count() > 0) {
            // Update existing delivery IDs to new format
            $deliveries = Delivery::orderBy('id')->get();
            $counter = 1;
            
            foreach ($deliveries as $delivery) {
                DB::table('deliveries')
                    ->where('id', $delivery->id)
                    ->update([
                        'deliveryId' => 'D-' . str_pad($counter, 4, '0', STR_PAD_LEFT)
                    ]);
                $counter++;
            }
        }
    }

    public function down(): void
    {
        // This is tricky to reverse, so you might leave it empty
        // or handle manually if needed
    }
};