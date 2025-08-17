<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use Barryvdh\DomPDF\Facade\Pdf; // make sure the package is installed

use App\Http\Requests\ProductRequest;
use App\Models\ProductCategories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductController extends Controller
{
    public function index()
    {
        $categories = ProductCategories::All();
        return view('superadmin.products.addProduct',compact('categories'));
    } 

    public function store(ProductRequest $request)
    {
        try { 

                // Determine the creator dynamically
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
                'product_code'          => $request['product_code'],
                'product_category_id'  => $request['product_category_id'], 
                'product_name'          => $request['product_name'], 
                'purchase_price'        => $request['purchase_price'],  
                'purchase_unit'         => $request['purchase_unit'], 
                'unit'                  => $request['unit'], 
                'remark'                => $request['remark'], 
                'created_by_id'         => $creatorId, 
                'created_by_type'       => $creatorType

            ]);
            
            return redirect()
                ->route('superadmin.products.addProduct')
                ->with('success', 'Product created successfully!');

        } catch (Exception $e) {
            Log::error('Product creation failed', ['error' => $e->getMessage()]);
            return back()->withErrors('An error occurred while saving the product.')->withInput();
             
        }
    } 


    public function update(ProductRequest $request, Product $product)
    {
      
        try { 
            $this->authorize('update', $product);  // Only creator can update
            $product->update($request->validated());
            return redirect()
                ->back()
                ->with('success', 'Product updated successfully!');

        } catch (Exception $e) {
            Log::error('Product update failed', ['error' => $e->getMessage()]);
            return back()->withErrors('An error occurred while updating the product.')->withInput();
        }
    } 


    public function destroy(Product $product)
    {
        try {
            
            $this->authorize('update', $product);  // Only creator can update
            $product->delete(); // soft delete
            return redirect()
                ->back()
                ->with('success', 'Product deleted successfully!');
        } catch (Exception $e) {
            Log::error('Product deletion failed', ['error' => $e->getMessage()]);
            return back()->withErrors('An error occurred while deleting the product.');
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
