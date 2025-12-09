<?php

namespace App\Exports\Reports;

use App\Models\BranchOffice;
use App\Models\Leave;
use App\Models\User;
use BcMath\Number;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeavesExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles, WithColumnFormatting
{

    public function collection()
    {
        $leaveService = new \App\Services\Report\LeaveService();
        return $leaveService->getReportsData(null, null, null, 'periode', 'desc');
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Name',
            'Branch',
            'Department',
            'Position',
            'Total Leave',
            'Total Sick',
            'Total Permission',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function map($leave): array
    {

        return [
            $leave->periode,
            $leave->user->name ? Str::title($leave->user->name) : '-',
            $leave->user->branch ? Str::title($leave->user->branch->name) : '-',
            $leave->user->department ? Str::title($leave->user->department->name) : '-',
            $leave->user->position ? Str::title($leave->user->position->name) : '-',
            !empty($leave->total_leave) ? (int) $leave->total_leave : 0,
            !empty($leave->total_sick) ? (int) $leave->total_sick : 0,
            !empty($leave->total_permission) ? (int) $leave->total_permission : 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
