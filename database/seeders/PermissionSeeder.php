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

            // Approval
            'approval.view',
            'approval.create',
            'approval.edit',
            'approval.delete',

            // Calibration
            'calibration.view',
            'calibration.create',
            'calibration.edit',
            'calibration.delete',

            // Document
            'document.view',
            'document.create',
            'document.edit',
            'document.delete',

            // Letter
            'letter.view',
            'letter.create',
            'letter.edit',
            'letter.delete',

            // IS Code
            'iscode.view',
            'iscode.create',
            'iscode.edit',
            'iscode.delete',

            // Profile
            'profile.view',
            'profile.create',
            'profile.edit',
            'profile.delete',
        ];

        foreach ($defaultPermissions as $permission) {
            Permission::firstOrCreate(
                ['permission_name' => $permission], // unique column in your table
                ['description' => ucfirst(str_replace('.', ' ', $permission))]
            );
        }
    }
}
