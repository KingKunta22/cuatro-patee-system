<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Seeder;
use Faker\Factory;

class PurchaseOrderItemSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        $measurements = ['kilogram', 'gram', 'liter', 'milliliter', 'pcs', 'set', 'pair', 'pack'];

        // Realistic pet shop products
        $petProducts = [
            // Dog Food
            'Premium Dry Dog Food - Chicken Formula',
            'Grain-Free Puppy Food - Salmon Recipe', 
            'Senior Dog Formula - Joint Care',
            'Small Breed Dry Dog Food',
            'Large Breed Adult Dog Food',
            
            // Cat Food
            'Indoor Cat Formula - Weight Control',
            'Kitten Food - Chicken & Rice',
            'Grain-Free Cat Food - Fish Recipe',
            'Hairball Control Cat Food',
            'Senior Cat Formula - Kidney Care',
            
            // Pet Accessories
            'Adjustable Nylon Dog Collar',
            'Retractable Dog Leash - 16ft',
            'Cat Scratching Post with Toy',
            'Pet Travel Carrier - Medium',
            'Automatic Pet Feeder',
            
            // Grooming
            'Oatmeal Pet Shampoo - Sensitive Skin',
            'Pet Grooming Brush - Shedding Control',
            'Pet Nail Clippers with Safety Guard',
            'Ear Cleaning Solution for Pets',
            'Pet Dental Care Kit',
            
            // Toys
            'Durable Rubber Chew Toy',
            'Interactive Cat Toy with Feathers',
            'Dog Puzzle Toy - Treat Dispenser',
            'Plush Squeaky Toy - Duck Shape',
            'Catnip-filled Mouse Toy',
            
            // Healthcare
            'Flea & Tick Prevention Drops',
            'Pet Vitamins - Multivitamin Formula',
            'Joint Supplement for Senior Pets',
            'Pet Calming Aid - Anxiety Relief',
            'Digestive Enzymes for Pets',
            
            // Treats
            'Dental Chews for Dogs',
            'Salmon Flavor Cat Treats',
            'Training Treats - Small Bites',
            'Natural Beef Jerky for Dogs',
            'Freeze-Dried Chicken Treats'
        ];

        // Get all purchase orders to add items to them
        $purchaseOrders = PurchaseOrder::all();

        foreach ($purchaseOrders as $order) {
            // Create between 3 and 6 items for each purchase order
            $numberOfItems = $faker->numberBetween(3, 6);
            $orderTotal = 0;

            // Shuffle products to get random selection
            $shuffledProducts = $faker->randomElements($petProducts, $numberOfItems);

            foreach ($shuffledProducts as $productName) {
                $quantity = $faker->numberBetween(10, 100);
                $unitPrice = $faker->randomFloat(2, 50, 500);
                $totalAmount = $quantity * $unitPrice;
                $orderTotal += $totalAmount;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'productName' => $productName,
                    'quantity' => $quantity,
                    'unitPrice' => $unitPrice,
                    'totalAmount' => $totalAmount,
                    'itemMeasurement' => $faker->randomElement($measurements),
                ]);
            }

            // Update the purchase order's total amount with the sum of its items
            $order->update(['totalAmount' => $orderTotal]);
        }
        $this->command->info('Purchase Order Items created with realistic pet products!');
    }
}