<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PurchaseListController extends Controller
{
    //
    public function __construct(){
        $this->middleware('permission:purchase.view'); 
    }
     public function index()
    {
        return view('superadmin.purchaselist.purchaseList');  
    }
}
