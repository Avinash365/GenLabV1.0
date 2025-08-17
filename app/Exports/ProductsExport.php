<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products->map(function($p, $i) {
            return [
                $i + 1,
                $p->product_name,
                $p->product_code,
                $p->category->name ?? 'N/A',
                $p->purchase_unit,
                number_format($p->purchase_price, 2, '.', ''),
                $p->unit,
                $p->invoice_no,
                $p->remark,
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Name',
            'Code',
            'Category',
            'Purchase Unit',
            'Purchase Price',
            'Unit',
            'Invoice No',
            'Remark',
        ];
    }
}