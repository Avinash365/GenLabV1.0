<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestAdmin extends Command
{
    protected $signature = 'make:test-admin';
    protected $description = 'Create a test admin (admin@example.com / admin123) if Admin model exists';

    public function handle()
    {
        if (!class_exists(\App\Models\Admin::class)) {
            $this->warn('Admin model not found. Skipping.');
            return self::SUCCESS;
        }

        try {
            $admin = \App\Models\Admin::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin',
                    'password' => Hash::make('admin123'),
                ]
            );
            $this->info('Admin ready: admin@example.com / admin123');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
