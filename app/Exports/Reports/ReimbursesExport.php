<?php

namespace App\Exports\Reports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReimbursesExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        $reimburseService = new \App\Services\ReimbursementService();
        return $reimburseService->getReportsData(null, null, null, 'periode', 'desc');
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Name',
            'Branch',
            'Department',
            'Position',
            'Total',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function map($reimburse): array
    {



        return [
            $reimburse->periode,
            $reimburse->user->name ? Str::title($reimburse->user->name) : '-',
            $reimburse->user->branch ? Str::title($reimburse->user->branch->name) : '-',
            $reimburse->user->department ? Str::title($reimburse->user->department->name) : '-',
            $reimburse->user->position ? Str::title($reimburse->user->position->name) : '-',
            (int) $reimburse->total_reimburse_amount,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
