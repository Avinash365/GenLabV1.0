<?php

namespace App\Http\Controllers\Product;

use App\Models\ProductStockEntry;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductStockEntryController extends Controller
{
    public function index()
    {
        try {
            
            $entries = ProductStockEntry::with('product')->latest()->paginate(10);
            
            return view('superadmin.products.addProductStockEntry', compact('entries'));
        }
         catch (\Exception $e) {
            
            Log::error("Error fetching stock entries: " . $e->getMessage());
            return back()->with('error', 'Failed to load stock entries.');
        }
    }

    public function create()
    {
        try {
            
            // $products = Product::pluck('product_name', 'product_code');
            $products = Product::select('product_code', 'product_name')
            ->orderBy('product_name')
            ->get();
            return view('superadmin.products.addProductStockEntry', compact('products'));
        
        } catch (\Exception $e) {
            Log::error("Error loading stock entry form: " . $e->getMessage());
            return back()->with('error', 'Failed to load form.');
        }
    }

    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code'   => 'required|exists:products,product_code',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_unit'  => 'nullable|string|max:50',
            'quantity'       => 'required|integer|min:1',
            'type'           => 'nullable|in:buy,sell',
            'remarks'        => 'nullable|string',
            'upload_bill'    => 'nullable|file|mimes:jpg,png,pdf|max:2048',
            'invoice_no'     => 'required|string|unique:product_stock_entries,invoice_no',
        ]);

        try {
            $validated['type']='buy'; 
            
            $validated['upload_bill'] = null;
            if ($request->hasFile('upload_bill')) {
                $validated['upload_bill'] = $request->file('upload_bill')->store('bills', 'public');
            }

            $product = Product::where('product_code', $validated['product_code'])->first();
            if (!$product) {
                return back()->with('error', 'Product not found.')->withInput();
            }

            if ($validated['type'] === 'sell' && ($validated['quantity'] > $product->unit)) {
                return back()->with('error', 'Cannot sell more than available stock.')->withInput();
            }

            ProductStockEntry::create($validated);

            // Update product unit
            if ($validated['type'] === 'buy') {
                $product->increment('unit', $validated['quantity']);
            } else {
                $product->decrement('unit', $validated['quantity']);
            }

            return redirect()->back()->with('success', 'Stock entry added successfully.');

        } catch (\Exception $e) {
            Log::error("Error creating stock entry: " . $e->getMessage());
            return back()->with('error', 'Failed to create stock entry.')->withInput();
        }
    }


    public function show(ProductStockEntry $productStockEntry)
    {
        try {
            return view('product_stock_entries.show', compact('productStockEntry'));
        } catch (\Exception $e) {
            Log::error("Error showing stock entry: " . $e->getMessage());
            return back()->with('error', 'Failed to show stock entry.');
        }
    }


    public function edit(ProductStockEntry $productStockEntry)
    {
        try {
            $products = Product::pluck('product_name', 'product_code');
            return view('product_stock_entries.edit', compact('productStockEntry', 'products'));
        } catch (\Exception $e) {
            Log::error("Error loading edit form: " . $e->getMessage());
            return back()->with('error', 'Failed to load edit form.');
        }
    }


    public function update(Request $request, ProductStockEntry $productStockEntry)
    {
        $validated = $request->validate([
            'product_code'   => 'required|exists:products,product_code',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_unit'  => 'nullable|string|max:50',
            'quantity'       => 'nullable|integer|min:0',
            'remarks'        => 'nullable|string',
            'upload_bill'    => 'nullable|file|mimes:jpg,png,pdf|max:2048',
            'invoice_no'     => "required|string|unique:product_stock_entries,invoice_no,{$productStockEntry->id}",
        ]);

        try {
            // Handle file upload
            if ($request->hasFile('upload_bill')) {
                $validated['upload_bill'] = $request->file('upload_bill')->store('bills', 'public');
            }

            // If product_code or quantity is changed, adjust stock
            if ($productStockEntry->product_code !== $validated['product_code'] || $productStockEntry->quantity != $validated['quantity']) {

                // Subtract old qty from old product
                $oldProduct = Product::where('product_code', $productStockEntry->product_code)->first();
                if ($oldProduct) {
                    $oldProduct->decrement('unit', $productStockEntry->quantity ?? 0);
                }

                // Add new qty to new product
                $newProduct = Product::where('product_code', $validated['product_code'])->first();
                if ($newProduct) {
                    $newProduct->increment('unit', $validated['quantity'] ?? 0);
                }
            }

            // Update stock entry record
            $productStockEntry->update($validated);

            return redirect()->back()->with('success', 'Stock entry updated successfully.');

        } catch (\Exception $e) {
            Log::error("Error updating stock entry: " . $e->getMessage());
            return back()->with('error', 'Failed to update stock entry.')->withInput();
        }
    }

    
    public function destroy(ProductStockEntry $productStockEntry)
    {
        try {
            $product = Product::where('product_code', $productStockEntry->product_code)->first();
            if ($product) {
                $product->decrement('unit', $productStockEntry->quantity ?? 0);
            }

            $productStockEntry->delete();

            return redirect()->back()->with('success', 'Stock entry deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting stock entry: " . $e->getMessage());
            return back()->with('error', 'Failed to delete stock entry.');
        }
    }
}
