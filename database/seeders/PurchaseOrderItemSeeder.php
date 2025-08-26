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

        // Get all purchase orders to add items to them
        $purchaseOrders = PurchaseOrder::all();

        foreach ($purchaseOrders as $order) {
            // Create between 1 and 5 items for each purchase order
            $numberOfItems = $faker->numberBetween(1, 5);
            $orderTotal = 0; // Let's calculate a total for the order

            for ($j = 0; $j < $numberOfItems; $j++) {
                $quantity = $faker->numberBetween(1, 100);
                $unitPrice = $faker->randomFloat(2, 50, 500);
                $totalAmount = $quantity * $unitPrice;
                $orderTotal += $totalAmount;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'productName' => $faker->words(3, true), // e.g., "Premium Dog Food"
                    'quantity' => $quantity,
                    'unitPrice' => $unitPrice,
                    'totalAmount' => $totalAmount,
                    'itemMeasurement' => $faker->randomElement($measurements),
                ]);
            }

            // Update the purchase order's total amount with the sum of its items
            $order->update(['totalAmount' => $orderTotal]);
        }
        $this->command->info('Purchase Order Items created and totals updated successfully!');
    }
}