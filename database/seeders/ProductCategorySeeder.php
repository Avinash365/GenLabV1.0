<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategories; // Import the model

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic items and gadgets'],
            ['name' => 'Clothing', 'description' => 'Men and Women clothing'],
            ['name' => 'Groceries', 'description' => 'Daily grocery items'],
            ['name' => 'Stationery', 'description' => 'Office and school supplies'],
            ['name' => 'Furniture', 'description' => 'Home and office furniture'],
        ];

        foreach ($categories as $category) {
            ProductCategories::create($category); // now refers to the model
        }
    }
}
