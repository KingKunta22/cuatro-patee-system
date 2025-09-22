<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            'Pedigree',
            'Whiskas', 
            'Purina',
            'Royal Canin',
            'Meow Mix',
            'Blue Buffalo',
            'Drools',
            'NutriCan',
            'Iams',
            'Hill\'s Science Diet'
        ];

        foreach ($brands as $brandName) {
            Brand::create([
                'productBrand' => $brandName
            ]);
        }

        $this->command->info('Brands created successfully!');
    }
}