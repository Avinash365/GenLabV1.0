<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RoleAndPermission;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index()
    {
        // Logic to list all users
        $roles = RoleAndPermission::all();
        $permissions = RoleAndPermission::pluck('permissions')->toArray();
        return view('superadmin.users.index', compact('roles', 'permissions'));
    }
    public function create()
    {
        $roles = RoleAndPermission::all(); // or your roles fetching logic
        $permissions = RoleAndPermission::pluck('role_name'); // or your permissions fetching logic

        return view('superadmin.users.create', compact('roles', 'permissions'));
    }
}
