<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:test-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test user for API testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if user already exists
        if (User::where('user_code', 'test123')->exists()) {
            $this->info('Test user already exists!');
            $this->info('User Code: test123');
            $this->info('Password: password123');
            return;
        }

        try {
            // Try to find a role, if none exist create a default one
            $role = Role::first();
            if (!$role) {
                $this->info('No roles found, creating default role...');
                $role = Role::create([
                    'role_name' => 'User',
                    'created_by' => 1
                ]);
            }

            // Create test user
            $user = User::create([
                'name' => 'Test User',
                'user_code' => 'test123', 
                'password' => Hash::make('password123'),
                'role_id' => $role->id,
                'created_by' => 1
            ]);

            $this->info('Test user created successfully!');
            $this->info('User Code: test123');
            $this->info('Password: password123');
            $this->info('Role: ' . $role->role_name);
            
        } catch (\Exception $e) {
            $this->error('Failed to create test user: ' . $e->getMessage());
        }
    }
}
