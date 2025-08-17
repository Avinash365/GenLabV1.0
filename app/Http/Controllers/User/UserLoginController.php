<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SuperAdminLoginService;
use App\Models\SuperAdmin;
use App\Enums\Role; 


class UserLoginController extends Controller
{
    /**
     * Show the login form for super admin.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('user.login');
    }

    /**
     * Handle the login request for super admin.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */ 
    public function index(){
        return view('user.login'); 
    }   
    
    public function login(Request $request)
        {
            $request->validate([
                'user_code' => 'required|string',
                'password'  => 'required|string',
            ]);

            $credentials = $request->only('user_code', 'password');

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                return redirect()->route('user.dashboard.index')
                    ->with('status', 'Logged in successfully');
            }

            return back()->withErrors([
                'user_code' => 'Invalid credentials.',
            ])->withInput();
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
