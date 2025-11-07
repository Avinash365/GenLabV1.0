<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Requclest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = ProductCategory::all();
            
            return view('superadmin.categories.index', compact('categories'));
        } 
        catch (\Exception $e) {
            Log::error('Failed to load categories', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Something went wrong while loading categories.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:product_categories,name,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
        ]);

        try {
            // Check if category exists but soft deleted
            $trashedCategory = ProductCategory::withTrashed()
                ->where('name', $validated['name'])
                ->first();

            if ($trashedCategory && $trashedCategory->trashed()) {
                // Restore the soft deleted category
                $trashedCategory->restore();

                // Update description if provided
                if (!empty($validated['description'])) {
                    $trashedCategory->description = $validated['description'];
                    $trashedCategory->save();
                }

                return redirect()->back()
                    ->with('success', 'Category restored successfully.');
            }

            // Otherwise create a new category
            ProductCategory::create([
                'name'        => $validated['name'],
                'slug'        => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
            ]);

            return redirect()->back()->with('success', 'Category created successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to create/restore category', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Something went wrong while creating the category.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $category)
    {
        try {
            return view('superadmin.categories.show', compact('productCategory'));

        } catch (\Exception $e) {
            Log::error('Failed to show category', ['error' => $e->getMessage()]);
            return redirect()->route('superadmin.categories.index')->with('error', 'Unable to display this category.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $category)
    {
        try {
            return view('superadmin.categories.edit', compact('productCategory'));
        } catch (\Exception $e) {
            Log::error('Failed to edit category', ['error' => $e->getMessage()]);
            return redirect()->route('superadmin.categories.index')->with('error', 'Unable to open edit form.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
                'name'        => 'required|string|max:255|unique:product_categories,name,' . $category->id,
                'description' => 'nullable|string',
            ]);

        try {
            
            $category->update([
                'name'        => $validated['name'],
                'slug'        => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
            ]);

            return redirect()->back()
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update category', [
                'id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Something went wrong while updating the category.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $category)
    {
        try { 
           
            $category->delete();

            return redirect()->back()
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete category', [
                'id' => $category->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Something went wrong while deleting the category.');
        }
    }
}
