<?php

namespace Database\Seeders;

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
            // User
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Roles
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',

            // Bookings
            'booking.view',
            'booking.create',
            'booking.edit',
            'booking.delete',

            // Products
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',

            // Departments
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',

            // Accounts
            'accounts.view',
            'accounts.create',
            'accounts.edit',
            'accounts.delete',

            // Content
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',

            // Settings
            'settings.view',
            'settings.create',
            'settings.edit',
            'settings.delete',
        ];

        foreach ($defaultPermissions as $permission) {
            Permission::firstOrCreate(
                ['permission_name' => $permission], // unique column in your table
                ['description' => ucfirst(str_replace('.', ' ', $permission))]
            );
        }
    }
}
