<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewBookingsSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('new_bookings')->insert([
                'marketing_id'     => '502',   // fixed
                'department_id'    => rand(1, 2), // must exist in departments table
                'client_name'      => 'Client ' . $i,
                'client_address'   => 'Address for Client ' . $i,
                'job_order_date'   => now()->subDays(rand(0, 5)),
                'report_issue_to'  => 'Person ' . $i,
                'reference_no'     => Str::uuid(),
                'contact_no'       => '98765432' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'contact_email'    => 'client' . $i . '@example.com',
                'hold_status'      => (bool)rand(0, 1),
                'upload_letter_path' => null,
                'created_by_id'    => rand(1, 2),
                'created_by_type'  => 'App\\Models\\User',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}