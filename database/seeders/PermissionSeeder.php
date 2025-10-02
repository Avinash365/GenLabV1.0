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

            // Inventory 
            'inventory.view', 
            'inventory.edit', 
            'inventory.create', 
            'inventory.delete', 


            //Reporting 
            'reporting.view', 
            'reporting.edit', 
            'reporting.create', 
            'reporting.delete', 

            // lab Analysts 
            'lab-analysts.view', 
            'lab-analysts.create', 
            'lab-analysts.edit', 
            'lab-analysts.delete', 


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

            // report formate 
            'report-format.view',
            'report-format.create',
            'report-format.edit',
            'report-format.delete', 

            // report generate 
            'report-generate.view',
            'report-generate.create',
            'report-generate.edit',
            'report-generate.delete',  


            // leave 
            'leave.view', 
            'leave.edit', 
            'leave.create', 
            'leave.delete', 

            // account 
            'account.view',
            'account.create',
            'account.edit',
            'account.delete',  

            //Web Settings 
            'web-settings.view', 
            'web-settings.edit', 

            // bank Settings 
            'bank-details.view', 
            'bank-details.edit', 


        ]; 

        foreach ($defaultPermissions as $permission) {
            Permission::firstOrCreate(
                ['permission_name' => $permission], // unique column in your table
                ['description' => ucfirst(str_replace('.', ' ', $permission))]
            );
        }
    }
}
