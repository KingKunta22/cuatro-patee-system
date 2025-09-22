<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Pet Food',
            'Pet Accessories',
            'Pet Grooming',
            'Pet Toys',
            'Pet Healthcare',
            'Pet Bedding',
            'Aquatic Supplies',
            'Pet Training',
            'Pet Cleaning & Hygiene',
            'Pet Treats'
        ];

        foreach ($categories as $categoryName) {
            Category::create([
                'productCategory' => $categoryName
            ]);
        }

        $this->command->info('Categories created successfully!');
    }
}