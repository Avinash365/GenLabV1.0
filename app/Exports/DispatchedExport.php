<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DispatchedExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected array $rows;

    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Job No', 'Client', 'Description', 'Dispatched At', 'Dispatched By'];
    }
}
