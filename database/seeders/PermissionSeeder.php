<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPermissions = [
            'user.manage',
            'role.manage',
            'booking.manage',
            'product.manage',
            'content.edit',
            'settings.update',
        ]; 

        foreach ($defaultPermissions as $permission) {
            Permission::firstOrCreate(
                ['permission_name' => $permission], // unique column
                ['description' => ucfirst(str_replace('.', ' ', $permission))]
            );
        }
    }
}
