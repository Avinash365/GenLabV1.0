<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IsCodesController extends Controller
{
   public function index()
    {
        return view('superadmin.iscodes.Iscode');  
    }
}
