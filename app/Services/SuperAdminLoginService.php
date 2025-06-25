<?php
// filepath: d:\laravel\EduGen\app\Services\SuperAdminLoginService.php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class SuperAdminLoginService
{
    /**
     * Attempt to log in as super admin.
     *
     * @param array $credentials
     * @return bool
     */
    public function login(array $credentials): bool
    {
        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();
            if ($user->role === 'super_admin') {
                return true;
            }
            // Not a super admin, log out
            Auth::guard('admin')->logout();
        }
        return false;
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