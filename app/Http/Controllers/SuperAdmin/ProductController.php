<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;



use Illuminate\Http\Request;

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

}
