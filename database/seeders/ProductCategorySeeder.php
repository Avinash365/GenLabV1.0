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
            // Ensure idempotent seeding and handle soft-deleted rows gracefully
            $existing = ProductCategories::withTrashed()
                ->where('name', $category['name'])
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'description' => $category['description'],
                ]);
            } else {
                ProductCategories::create($category);
            }
        }
    }
}
