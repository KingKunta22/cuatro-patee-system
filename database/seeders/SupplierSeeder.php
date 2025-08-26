<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Faker\Factory;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        // Create 5 Dummy Suppliers
        for ($i = 0; $i < 5; $i++) {
            Supplier::create([
                'supplierName' => ucfirst($faker->word()),
                'supplierAddress' => $faker->address,
                'supplierContactNumber' => '09' . $faker->numerify('#########'),
                'supplierEmailAddress' => $faker->companyEmail,
                'supplierStatus' => 'Active',
            ]);
        }
        $this->command->info('Suppliers created successfully!');
    }
}