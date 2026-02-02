<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SuperAdminLoginService;
use App\Models\SuperAdmin;
use App\Enums\Role; 
use App\Models\SiteSetting;
use Carbon\Carbon;

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
                
                $user = Auth::user();

                // Check for time restriction
                if ($user->role && $user->role->restrict_login_after_6pm) {
                    $setting = SiteSetting::first();
                    $startTime = $setting->restriction_start_time ? Carbon::parse($setting->restriction_start_time) : Carbon::createFromTime(18, 0, 0);
                    $endTime = $setting->restriction_end_time ? Carbon::parse($setting->restriction_end_time) : Carbon::createFromTime(8, 0, 0);
                    
                    $now = now();
                    $current = Carbon::createFromTime($now->hour, $now->minute, $now->second);
                    $start = Carbon::createFromTime($startTime->hour, $startTime->minute, $startTime->second);
                    $end = Carbon::createFromTime($endTime->hour, $endTime->minute, $endTime->second);

                    $isRestricted = false;

                    if ($start->gt($end)) {
                        // Overnight restriction (e.g. 18:00 to 08:00)
                        if ($current->gte($start) || $current->lt($end)) {
                           $isRestricted = true;
                        }
                    } else {
                        // Same day restriction (e.g. 09:00 to 17:00)
                        if ($current->gte($start) && $current->lt($end)) {
                            $isRestricted = true;
                        }
                    }

                    if ($isRestricted) {
                        Auth::logout();
                        $startStr = $start->format('g:i A');
                        $endStr = $end->format('g:i A');
                        return back()->withErrors([
                            'user_code' => "Access denied: Login is restricted for your role between $startStr and $endStr.",
                        ])->withInput();
                    }
                }
                
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
