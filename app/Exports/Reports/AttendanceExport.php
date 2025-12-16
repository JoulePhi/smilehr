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

class AttendanceExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        $attendanceService = new \App\Services\AttendanceService();
        return $attendanceService->getReportsData(null, null, null, 'periode', 'desc');
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Name',
            'Branch',
            'Department',
            'Position',
            'Cost Center',
            'Total Worked',
            'Total Visit',
            'Total Invalid',
            'Total Late',
            'Total Early Leave',
            'Total Overtime',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function map($attendance): array
    {



        return [
            $attendance->periode,
            $attendance->user->name ? Str::title($attendance->user->name) : '-',
            $attendance->user->branch ? Str::title($attendance->user->branch->name) : '-',
            $attendance->user->department ? Str::title($attendance->user->department->name) : '-',
            $attendance->user->position ? Str::title($attendance->user->position->name) : '-',
            $attendance->user->costCenter ? Str::title($attendance->user->costCenter->name) : '-',
            $attendance->total_worked_days,
            $attendance->total_visit,
            $attendance->total_invalid_count,
            $attendance->total_late_minutes,
            $attendance->total_early_minutes,
            $attendance->total_overtime_minutes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
