<?php

namespace App\Exports\Reports;

use App\Models\BranchOffice;
use App\Models\User;
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

class EmployeeExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        return User::with(['branch', 'department', 'position'])->employee()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIK',
            'Name',
            'Branch',
            'Department',
            'Position',
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }

    public function map($user): array
    {

        return [
            $user->id,
            $user->nik,
            Str::title($user->name),
            $user->branch ? Str::title($user->branch->name) : '-',
            $user->department ? Str::title($user->department->name) : '-',
            $user->position ? Str::title($user->position->name) : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
