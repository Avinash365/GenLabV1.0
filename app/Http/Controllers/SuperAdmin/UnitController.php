<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    //
     public function index()
    {
        return view('superadmin.unit.Unit');  
    }
}
