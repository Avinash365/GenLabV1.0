<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Menu Configuration (Single Source of Truth)
        |--------------------------------------------------------------------------
        */
        $menus = [

            'booking' => ['booking'],

            'inventory' => [
                'product', 'category', 'store', 'supplier', 'unit',
                'purchase', 'issue',
            ],

            'reporting' => [
                'report-format',
                'report_received',
                'report_hold',
                'report_reported',
                'report_pending',
                'report_print_upload',
                'report_analyst_activity',
                'report_format',
                'report_generate',
                'report_dispatch',
            ],

            'hr' => [
                'employee',
                'leave',
                'attendance',
                'manual_attendance', 
                'biometric_attendance', 
                'holiday',
                'payroll',
                'approve_leave',
            ],

            'accounts' => [
                'client_assigned',
                'client_ledger', 
                'marketing_ledger',
                'invoice',
                'quotation',
                'amount_approved',
                'invoice_payment',
                'cash_letter',
                'cash_payment',
                'bank_transaction',
                'purchase_bill',
                'employee_salary',
                'cleared_expense',
                'cheque',
                'cheque_template',
                'blank_invoice', 
            ],

            'attachments' => [
                'iscode',
                'calibration',
                'profile',
                'approval',
                'letter',
                'document',
            ],

            'expenses' => [
                'personal_expense',
                'marketing_expense',
                'office_expense',
                'approve_expense',
                'reject_expense',
            ],

            'transportation' => [
                'meter_reading',
                'vehicle_registration',
            ],

            'settings' => [
                'department',
                'role',
                'user',
                'web_setting',
                'bank_detail',
            ],

            'others' => [
                'report',
                'sample_cell',
                'remanent_cell',
                'reception',
                'qlr',
                'client',
                'audit_trail',
                'lab-analysts', 
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Default Actions
        |--------------------------------------------------------------------------
        */
        $defaultActions = ['view', 'create', 'edit', 'delete'];

        /*
        |--------------------------------------------------------------------------
        | Action Overrides (Important)
        |--------------------------------------------------------------------------
        */
        $customActions = [
            'report_received'     => ['view'],
            'audit_trail'         => ['view'],
            'approve_expense'     => ['view'],
            'reject_expense'      => ['view'],
        ];

        /*
        |--------------------------------------------------------------------------
        | Generate Permissions
        |--------------------------------------------------------------------------
        */
        foreach ($menus as $modules) {
            foreach ($modules as $module) {

                $module = Str::snake(strtolower($module));

                $actions = $customActions[$module] ?? $defaultActions;

                foreach ($actions as $action) {
                    Permission::firstOrCreate(
                        ['permission_name' => "{$module}.{$action}"],
                        ['description' => ucfirst(str_replace('_', ' ', "{$module} {$action}"))]
                    );
                }
            }
        }
    }
}