<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Faker\Factory;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        // Create 10 Dummy Customers
        for ($i = 0; $i < 10; $i++) {
            Customer::create([
                'customerName' => $faker->firstName(),
                'customerAddress' => $faker->address,
                'customerContactNumber' => '09' . $faker->numerify('###########'),
                'customerEmailAddress' => $faker->safeEmail,
                'customerStatus' => 'Active',
            ]);
        }
        $this->command->info('Customers created successfully!');
    }
}