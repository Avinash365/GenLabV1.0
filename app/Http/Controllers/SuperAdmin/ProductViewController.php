<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategories;
use Illuminate\Http\Request;


class ProductViewController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->paginate(10);
        $productCategories = ProductCategories::all();
        return view('superadmin.viewproduct.viewProduct', compact('products','productCategories'));
    }     

}
