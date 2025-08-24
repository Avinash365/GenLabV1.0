<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            $invoice = 'INV-' . str_pad($i, 5, '0', STR_PAD_LEFT);

            $data = [
                'product_code' => 'PRD-' . strtoupper(Str::random(6)),
                'product_name' => 'Sample Product ' . $i,
                'purchase_price' => fake()->randomFloat(2, 100, 1000),
                'purchase_unit' => 'kg',
                'unit' => rand(1, 20),
                'remark' => 'This is a sample remark for product ' . $i,
                'created_by_id' => 1,
                'created_by_type' => 'App\\Models\\Admin',
            ];

            $existing = Product::withTrashed()->where('invoice_no', $invoice)->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                // Keep category stable on reseed; only refresh other fields
                $existing->update($data);
            } else {
                Product::create(array_merge($data, [
                    'product_category_id' => rand(1, 4),
                    'invoice_no' => $invoice,
                ]));
            }
        }
    }
}
