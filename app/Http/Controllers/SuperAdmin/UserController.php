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
        // Logic to show form for creating a new user
        return view('superadmin.users.create');
    }
}
