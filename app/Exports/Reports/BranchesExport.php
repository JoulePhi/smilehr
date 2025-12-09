<?php

namespace App\Exports\Reports;

use App\Models\BranchOffice;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BranchesExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        return BranchOffice::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Alamat',
            'Longitude',
            'Latitude',
            'Radius (m)',
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }

    public function map($branch): array
    {

        return [
            $branch->id,
            $branch->name,
            $branch->address,
            $branch->longitude,
            $branch->latitude,
            $branch->radius,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
