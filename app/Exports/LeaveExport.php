<?php

namespace App\Exports;

use App\Models\Leave;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Leave::with('user', 'user.branch', 'user.department', 'user.position', 'user.costCenter')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Karyawan',
            'Cabang',
            'Departemen',
            'Jabatan',
            'Cost Center',
            'Tanggal',
            'Tipe',
            'Keterangan',
            'Approved'
        ];
    }

    public function map($leave): array
    {
        if ($leave->date != null) {
            $leave->date =  new Carbon($leave->date);
        }
        $formattedDate = $leave->date ? $leave->date->format('D, Y-m-d') : '';
        return [
            $leave->id,
            $leave->user?->name,
            $leave->user?->branch?->name,
            $leave->user?->department?->name,
            $leave->user?->position?->name,
            $leave->user?->costCenter?->name,
            $formattedDate,
            $leave->type,
            $leave->remarks,
            $leave->is_approved ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
