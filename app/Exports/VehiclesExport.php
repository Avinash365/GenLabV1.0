<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VehiclesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $vehicles;

    public function __construct(Collection $vehicles)
    {
        $this->vehicles = $vehicles;
    }

    public function collection()
    {
        return $this->vehicles;
    }

    public function headings(): array
    {
        return [
            'Vehicle Name',
            'Engine Number',
            'Handed Over Person',
            'Insurance Expiry Date',
            'PUCC Expiry Date',
            'Registration Date',
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->name,
            $vehicle->engine_number,
            $vehicle->handed_over_person,
            optional($vehicle->rc_expiry_date)->format('d-m-Y'),
            optional($vehicle->puc_expiry_date)->format('d-m-Y'),
            optional($vehicle->created_at)->format('d-m-Y'),
        ];
    }
}
