<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\NewBooking;
use App\Models\BookingItem;
use App\Models\Admin;
use Faker\Factory as Faker;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get all admin IDs
        $adminIds = Admin::pluck('id')->toArray();
        if (empty($adminIds)) {
            $this->command->info('No admins found in the admins table. Seeder aborted.');
            return;
        }

        // Create 20 bookings
        for ($i = 1; $i <= 20; $i++) {
            $booking = NewBooking::create([
                'admin_id' => $faker->randomElement($adminIds),
                'client_name' => $faker->name(),
                'client_address' => $faker->address(),
                'client_email' => $faker->safeEmail(),
                'client_phone' => $faker->phoneNumber(),
                'job_order_date' => $faker->date(),
                'report_issue_to' => $faker->name(),
                'reference_no' => strtoupper(Str::random(8)),
                'marketing_code' => strtoupper(Str::random(5)),
                'contact_no' => $faker->phoneNumber(),
                'contact_email' => $faker->safeEmail(),
                'contractor_name' => $faker->company(),
                'hold_status' => $faker->boolean(),
                'upload_letter_path' => null,
            ]);

            // Each booking has 1-5 items
            $itemCount = rand(1, 5);
            for ($j = 1; $j <= $itemCount; $j++) {
                BookingItem::create([
                    'new_booking_id' => $booking->id,
                    'sample_description' => $faker->word(),
                    'sample_quality' => $faker->word(),
                    'lab_expected_date' => $faker->date(),
                    'amount' => $faker->randomFloat(2, 100, 1000),
                    'lab_analysis' => $faker->word(),
                    'job_order_no' => strtoupper(Str::random(6)),
                ]);
            }
        }
    }
}
