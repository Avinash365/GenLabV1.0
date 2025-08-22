<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department; 


class DashboardController extends Controller
{
    /**
     * Display the Super Admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // $departments = Department::where('is_active', 1)->get();

        if (auth()->guard('admin')->check()) {
            $departments = Department::where('is_active', 1)->get();
            return view('superadmin.dashboard', compact('departments'));
        }

        $user = auth()->user();
        $departments = Department::where('is_active', 1)
        ->whereHas('permissions', function ($q) use ($user) {
            $q->whereIn('permissions.id', $user->permissions->pluck('id'));
        })
        ->get(); 
        
        return view('superadmin.dashboard', compact('departments'));
    }
}