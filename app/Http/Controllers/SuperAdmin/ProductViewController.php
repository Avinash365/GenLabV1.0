<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf; // require barryvdh/laravel-dompdf if not installed
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Models\ProductCategories;


class ProductViewController extends Controller
{
    public function index(Request $request, $categoryId = null)
    {
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

        $productCategories = ProductCategories::all();

        return view('superadmin.viewproduct.viewProduct', compact('products', 'productCategories', 'categoryId', 'search'));
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
        $query = \App\Models\Product::with('category');

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

        $callback = function() use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['#','Name','Code','Category','Purchase Unit','Purchase Price','Unit','Invoice No','Remark']);
            foreach ($products as $i => $p) {
                fputcsv($handle, [
                    $i + 1,
                    $p->product_name,
                    $p->product_code,
                    $p->category->name ?? 'N/A',
                    $p->purchase_unit,
                    number_format($p->purchase_price, 2, '.', ''),
                    $p->unit,
                    $p->invoice_no,
                    $p->remark,
                ]);
            }
            fclose($handle);
        };

        $filename = 'products.csv';
        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
