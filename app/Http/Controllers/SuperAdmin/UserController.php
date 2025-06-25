<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index()
    {
        // Logic to list all users
        return view('superadmin.users.index');
    }
    public function create()
    {
        // Logic to show form for creating a new user
        return view('superadmin.users.create');
    }
}
