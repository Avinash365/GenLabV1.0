<?php

namespace App\Http\Controllers\SuperAdmin;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;


use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use Barryvdh\DomPDF\Facade\Pdf;       // make sure the package is installed



use Exception;


class ProductController extends Controller
{
    public function index()
    {
        $this->authorize('view', Product::class);

        $categories = ProductCategory::all();
        return view('superadmin.Products.addProduct', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);  // Handled by global handler

        try {
            
            // Determine creator dynamically
            if (auth('admin')->check()) {
                $creatorId = auth('admin')->id();
                $creatorType = 'App\\Models\\Admin';
            } elseif (auth('web')->check()) {
                $creatorId = auth('web')->id();
                $creatorType = 'App\\Models\\User';
            } else {
                abort(403, 'Unauthorized');
            }

            Product::create([
                'product_code'         => $request->product_code,
                'product_category_id'  => $request->product_category_id,
                'product_name'         => $request->product_name,
                'purchase_price'       => null,
                'purchase_unit'        => $request->purchase_unit,
                'unit'                 => 0,
                'remark'               => $request->remark,
                'created_by_id'        => $creatorId,
                'created_by_type'      => $creatorType,
            ]);

            return redirect()
                ->route('superadmin.products.addProduct')
                ->with('success', 'Product created successfully!');

        } catch (Exception $e) {
            Log::error('Product creation failed', ['error' => $e->getMessage()]);
            return back()->withErrors('An error occurred while creating the product. Please try again.')->withInput();
        }
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product); // Global handler manages message

        try {
            $product->update($request->validated());
            return redirect()->back()->with('success', 'Product updated successfully!');
        } catch (Exception $e) {
            Log::error('Product update failed', ['error' => $e->getMessage()]);
            return back()->withErrors('An error occurred while updating the product. Please try again.')->withInput();
        }
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);  // Global handler manages message

        try {
            $product->delete(); // soft delete
            return redirect()->back()->with('success', 'Product deleted successfully!');
        } catch (Exception $e) {
            Log::error('Product deletion failed', ['error' => $e->getMessage()]);
            return back()->withErrors('An error occurred while deleting the product. Please try again.');
        }
    }
    public function exportPdf(Request $request, $category = null)
    {
        $query = Product::with('category');

        if ($category) {
            $query->where('product_category_id', $category);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('product_name', 'like', "%{$s}%")
                  ->orWhere('product_code', 'like', "%{$s}%");
            });
        }

        $products = $query->orderBy('product_name')->get();

        $pdf = Pdf::loadView('superadmin.viewproduct.products_pdf', compact('products'))
                  ->setPaper('a4', 'landscape');

        return $pdf->stream('products.pdf');
    }

    public function exportExcel(Request $request, $category = null)
    {
        $query = Product::with('category');

        if ($category) {
            $query->where('product_category_id', $category);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('product_name', 'like', "%{$s}%")
                  ->orWhere('product_code', 'like', "%{$s}%");
            });
        }

        $products = $query->orderBy('product_name')->get();

        return Excel::download(new ProductsExport($products), 'products.xlsx');
    }

}
