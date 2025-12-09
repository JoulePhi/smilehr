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

class OvertimesExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        $overtimeService = new \App\Services\Report\OvertimeService();
        return $overtimeService->getReportsData(null, null, null, 'periode', 'desc');
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
            'Fee',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function map($overtime): array
    {
        $overtime_fee = 0;

        if ($overtime->user->financial) {
            $overtime_fee = app(\App\Services\Report\OvertimeService::class)->calculateFee(
                $overtime->user->financial->overtime_calculation_method,
                $overtime->user->financial->hourly_wages_based_on,
                $overtime->user->financial->basic_salary,
                $overtime->user->financial->fixed_allowance,
                $overtime->total_hours,
                $overtime->user->financial->overtime_multiplier
            );
        }


        return [
            $overtime->periode,
            $overtime->user->name ? Str::title($overtime->user->name) : '-',
            $overtime->user->branch ? Str::title($overtime->user->branch->name) : '-',
            $overtime->user->department ? Str::title($overtime->user->department->name) : '-',
            $overtime->user->position ? Str::title($overtime->user->position->name) : '-',
            (int) $overtime->total_overtimes,
            (int) $overtime_fee,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
