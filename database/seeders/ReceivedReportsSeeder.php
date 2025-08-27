<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\User;
use App\Models\NewBooking;
use App\Models\BookingItem;

class ReceivedReportsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure at least one admin exists
        $admin = Admin::first();
        if (!$admin) {
            $this->command->warn('No admins found. Run AdminSeeder first.');
            return;
        }

        // Ensure roles exist
        $roles = [
            'analyst' => null,
            'marketing' => null,
        ];
        foreach (array_keys($roles) as $roleName) {
            $roleId = DB::table('roles')->where('role_name', $roleName)->value('id');
            if (!$roleId) {
                $roleId = DB::table('roles')->insertGetId([
                    'role_name' => $roleName,
                    'description' => ucfirst($roleName) . ' role',
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]);
            }
            $roles[$roleName] = $roleId;
        }

        // Create marketing user
        $marketingUser = User::firstOrCreate(
            ['user_code' => 'MKT-001'],
            [
                'name' => 'Marketing One',
                'password' => Hash::make('password123'),
                'role_id' => $roles['marketing'],
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        // Create two analysts
        $analystA = User::firstOrCreate(
            ['user_code' => 'ANL-001'],
            [
                'name' => 'Analyst Alpha',
                'password' => Hash::make('password123'),
                'role_id' => $roles['analyst'],
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );
        $analystB = User::firstOrCreate(
            ['user_code' => 'ANL-002'],
            [
                'name' => 'Analyst Beta',
                'password' => Hash::make('password123'),
                'role_id' => $roles['analyst'],
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        // Create 5 bookings with 2 items each
        for ($i = 1; $i <= 5; $i++) {
            $ref = 'REF' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
            $booking = NewBooking::updateOrCreate(
                ['reference_no' => $ref],
                [
                'marketing_id' => $marketingUser->id,
                'client_name' => 'Client ' . $i,
                'client_address' => '123 Street, City ' . $i,
                'job_order_date' => now()->subDays(10 - $i)->toDateString(),
                'report_issue_to' => 'lab',
                'contact_no' => '99999' . str_pad((string)$i, 5, '0', STR_PAD_LEFT),
                'contact_email' => 'client' . $i . '@example.com',
                'contractor_name' => 'Contractor ' . $i,
                'hold_status' => false,
                'upload_letter_path' => null,
                'created_by_id' => $admin->id,
                'created_by_type' => Admin::class,
                ]
            );

            // Items
            for ($j = 1; $j <= 2; $j++) {
                $analyst = ($j % 2 === 0) ? $analystA : $analystB;
                $jobNo = 'JOB-' . str_pad((string)$i, 3, '0', STR_PAD_LEFT) . '-' . chr(64 + $j);
                $item = BookingItem::updateOrCreate(
                    ['job_order_no' => $jobNo],
                    [
                        'new_booking_id' => $booking->id,
                        'sample_description' => 'Sample ' . $i . '-' . $j,
                        'sample_quality' => 'Q' . $j,
                        'lab_expected_date' => now()->addDays($j + $i)->toDateString(),
                        'amount' => 500 + ($i * 10) + ($j * 5),
                        'lab_analysis_code' => $analyst->user_code,
                    ]
                );

                // Mark some as already received to see both states
                if ($i === 1 && $j === 1) {
                    $item->received_by_id = $admin->id; // received by an admin for demo
                    $item->received_at = now()->subDay();
                    $item->save();
                }
            }
        }
    }
}
