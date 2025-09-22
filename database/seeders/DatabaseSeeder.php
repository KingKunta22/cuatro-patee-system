<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Run your seeders in the CORRECT order
        $this->call([
            BrandSeeder::class,           // First: Independent tables
            CategorySeeder::class,        // Second: Independent tables  
            SubcategorySeeder::class,     // Third: Independent tables
            SupplierSeeder::class,        // Fourth: Independent tables
            PurchaseOrderSeeder::class,   // Fifth: Needs suppliers
            PurchaseOrderItemSeeder::class, // Sixth: Needs purchase orders
            // UserSeeder::class,         // Add your user seeder here too!
        ]);
    }
}