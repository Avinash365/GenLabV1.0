<?php
// filepath: d:\laravel\EduGen\app\Services\SuperAdminLoginService.php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Enums\Role;

class SuperAdminLoginService
{
    /**
     * Attempt to log in as super admin.
     *
     * @param array $credentials
     * @return bool
     */
    public function login(array $credentials): ?Role
    {
        if (Auth::guard('admin')->attempt($credentials)) {
            
            $user = Auth::guard('admin')->user();
            
            if (in_array($user->role, [Role::ADMIN->value, Role::SUPER_ADMIN->value])) {
                return Role::from($user->role);
            }

            // Not a super admin, log out
            Auth::guard('admin')->logout();
        }
        return null;
    }

    /**
     * Log out the current super admin.
     *
     * @return void
     */
    public function logout(): void
    {
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}