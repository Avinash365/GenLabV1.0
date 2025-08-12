<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SuperAdminLoginService;
use App\Models\SuperAdmin;
use App\Enums\Role; 

class LoginController extends Controller
{
    /**
     * Show the login form for super admin.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('superadmin.auth.login');
    }

    /**
     * Handle the login request for super admin.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $loginService = app(SuperAdminLoginService::class);

        $role = $loginService->login($request->only('email', 'password')); 

        return match ($role) {
            Role::SUPER_ADMIN => redirect()->route('superadmin.dashboard.index')
                ->with('status', 'Logged in successfully as Super Admin'),

            Role::ADMIN => redirect()->route('admin.dashboard.index')
                ->with('status', 'Logged in successfully as Admin'),

            default => back()->withErrors(['email' => 'Invalid credentials'])->withInput(),
        };
    }

    /**
     * Handle the logout request for super admin.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        $loginService = app(SuperAdminLoginService::class);
        $loginService->logout();

        return redirect()->route('superadmin.login')->with('status', 'Logged out successfully');
    }

   
}
