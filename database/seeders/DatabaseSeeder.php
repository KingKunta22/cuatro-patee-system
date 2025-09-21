<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Run your seeders in the CORRECT order
        $this->call([
            SupplierSeeder::class,      // First: Independent tables
            PurchaseOrderSeeder::class, // Second: Needs suppliers
            PurchaseOrderItemSeeder::class, // Third: Needs purchase orders
            // UserSeeder::class,       // Add your user seeder here too!
        ]);
    }
}