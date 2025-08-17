<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Super Admin 1',
                'email' => 'superadmin1@example.com',
                'password' => Hash::make('password123'),
                'role' => 'super_admin'
            ],
            [
                'name' => 'Super Admin 2',
                'email' => 'superadmin2@example.com',
                'password' => Hash::make('password123'),
                'role' => 'super_admin'
            ],
            [
                'name' => 'Admin 1',
                'email' => 'admin1@example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin'
            ],
            [
                'name' => 'Admin 2',
                'email' => 'admin2@example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin'
            ]
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        }
    }
}
