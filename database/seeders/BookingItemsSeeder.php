 <?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingItemsSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('booking_items')->insert([
                'new_booking_id'    => $i,      // assumes new_bookings has IDs 1–10
                'lab_analysis_code' => '702',   // fixed
                'job_order_no'      => 'JOB-2025-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'sample_quality'    => ['High', 'Medium', 'Low'][array_rand(['High', 'Medium', 'Low'])],
                'amount'            => rand(1000, 5000) + (rand(0, 99) / 100),
                'particulars'       => 'Particulars ' . $i,
                'sample_description'=> 'Sample description for job ' . $i,
                'lab_expected_date' => now()->addDays(rand(5, 15)),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }
}