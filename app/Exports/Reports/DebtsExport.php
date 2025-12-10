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

class DebtsExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        $debtService = new \App\Services\Report\DebtService();
        return $debtService->getReportsData(null, null, null, 'periode', 'desc');
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

    public function map($debt): array
    {



        return [
            $debt->periode,
            $debt->user->name ? Str::title($debt->user->name) : '-',
            $debt->user->branch ? Str::title($debt->user->branch->name) : '-',
            $debt->user->department ? Str::title($debt->user->department->name) : '-',
            $debt->user->position ? Str::title($debt->user->position->name) : '-',
            (int) $debt->total_debt_amount,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
