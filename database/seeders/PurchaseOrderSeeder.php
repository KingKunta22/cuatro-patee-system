<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Faker\Factory;
use Illuminate\Support\Arr;

class PurchaseOrderSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        $supplierIds = Supplier::all()->pluck('id')->toArray();
        $year = date('Y'); // Get the current year

        // Let's create 8 dummy purchase orders
        for ($i = 0; $i < 8; $i++) {
            // Generate the order number using your application's logic
            $orderNumber = $this->generateOrderNumber($year, $i);

            PurchaseOrder::create([
                'orderNumber' => $orderNumber, // Use the generated number
                'supplierId' => Arr::random($supplierIds),
                'paymentTerms' => $faker->randomElement(['Online', 'Cash on Delivery']),
                'deliveryDate' => $faker->dateTimeBetween('+1 week', '+1 month'),
                'totalAmount' => 0, // Start with 0, will be updated by PurchaseOrderItemSeeder
            ]);
        }
        $this->command->info('Purchase Orders created successfully!');
    }

    // COPY OF THE generateOrderNumber METHOD
    private function generateOrderNumber($year, $index) 
    {
        // We use the $index to simulate the "last order" for the seeder
        // This ensures numbers are PO-2025-0001, PO-2025-0002, etc.
        $nextNumber = $index + 1;
        
        // Format: PO-2025-0001
        return "PO-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}