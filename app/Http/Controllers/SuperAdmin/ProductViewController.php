<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;


class ProductViewController extends Controller
{
    public function index()
    {
        $products = Product::paginate(10);
        return view('superadmin.viewproduct.viewProduct', compact('products'));
    }     

}
