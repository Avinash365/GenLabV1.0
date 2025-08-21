<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;


class ProductViewController extends Controller
{
    public function index(Request $request, $categoryId = null)
    {
        $this->authorize('view', Product::class);
       
        $search = $request->input('search');

        $products = Product::with('category')
            ->when($categoryId, fn($q) => $q->where('product_category_id', $categoryId))
            ->when($search, function($q) use ($search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('product_name', 'like', "%$search%")
                        ->orWhere('product_code', 'like', "%$search%");
                });
            })
            ->paginate(10);

        $productCategories = ProductCategory::all();

        return view('superadmin.viewproduct.viewProduct', compact('products', 'productCategories', 'categoryId', 'search'));
    }   
}
