<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 30; $i++) {
            Product::create([
                'admin_id' => ($i % 4) + 1, // Alternate between admin 1 and 2
                'product_code' => strtoupper($faker->unique()->bothify('P####')),
                'product_name' => ucfirst($faker->words(rand(2, 4), true)),
                'purchase_category' => $faker->randomElement(['Electronics', 'Groceries', 'Clothing', 'Stationery', 'Furniture']),
                'purchase_unit' => $faker->randomElement(['Kg', 'Gram', 'Piece', 'Dozen']),
                'sales_unit' => $faker->randomElement(['Kg', 'Gram', 'Piece', 'Dozen']),
                'unit_ratio' => rand(1, 10) . ':' . rand(1, 5),
                'sales_price' => $faker->randomFloat(2, 50, 500),
                'remarks' => $faker->optional()->sentence(),
            ]);
        }
    }
}
