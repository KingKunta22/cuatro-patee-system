<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    public function run()
    {
        $subcategories = [
            'Dog Food',
            'Cat Food',
            'Bird Food',
            'Fish Food',
            'Reptile Food',
            'Leashes & Collars',
            'Pet Shampoo & Conditioner',
            'Cat Litter & Accessories',
            'Chew Toys & Interactive Toys',
            'Vitamins & Supplements'
        ];

        foreach ($subcategories as $subcategoryName) {
            Subcategory::create([
                'productSubcategory' => $subcategoryName
            ]);
        }

        $this->command->info('Subcategories created successfully!');
    }
}