<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('departments')->insert([
            [
                'name'       => 'Marketing',
                'codes'      => json_encode(['MKT']),
                'description'=> 'Handles marketing and advertising',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Sales',
                'codes'      => json_encode(['SAL']),
                'description'=> 'Responsible for sales and client management',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Technology',
                'codes'      => json_encode(['TECH', 'DEV']),
                'description'=> 'Manages IT infrastructure and software development',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Vendor',
                'codes'      => json_encode(['VEN']),
                'description'=> 'Handles vendor relations',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Quality',
                'codes'      => json_encode(['QLT']),
                'description'=> 'Quality assurance and testing',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
