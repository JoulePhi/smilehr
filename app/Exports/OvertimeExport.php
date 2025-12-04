<?php

namespace App\Exports;

use App\Models\Overtime;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OvertimeExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Overtime::with('user', 'user.branch', 'user.department', 'user.position', 'user.costCenter')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Karyawan',
            'Tipe',
            'Cabang',
            'Departemen',
            'Jabatan',
            'Cost Center',
            'Masuk',
            'Keluar',
            'Keterangan',
            'Approved'
        ];
    }

    public function map($overtime): array
    {
        if ($overtime->shift_in_date != null) {
            $overtime->shift_in_date =  new Carbon($overtime->shift_in_date);
        }
        if ($overtime->shift_out_date != null) {
            $overtime->shift_out_date =  new Carbon($overtime->shift_out_date);
        }

        if ($overtime->shift_in != null) {
            $overtime->shift_in = Carbon::createFromFormat('H:i:s', $overtime->shift_in);
        }
        if ($overtime->shift_out != null) {
            $overtime->shift_out = Carbon::createFromFormat('H:i:s', $overtime->shift_out);
        }



        $formattedShiftInDate = $overtime->shift_in_date ? $overtime->shift_in_date->format('D, Y-m-d') : '';
        $formattedShiftOutDate = $overtime->shift_out_date ? $overtime->shift_out_date->format('D, Y-m-d') : '';

        $formattedShiftIn = $overtime->shift_in ? $overtime->shift_in->format('H:i') : '';
        $formattedShiftOut = $overtime->shift_out ? $overtime->shift_out->format('H:i') : '';



        return [
            $overtime->id,
            $overtime->user?->name,
            $overtime->is_special ? 'Special Overtime' : 'Overtime',
            $overtime->user?->branch?->name,
            $overtime->user?->department?->name,
            $overtime->user?->position?->name,
            $overtime->user?->costCenter?->name,
            $formattedShiftInDate . ' ' . $formattedShiftIn,
            $formattedShiftOutDate . ' ' . $formattedShiftOut,
            $overtime->remarks,
            $overtime->is_approved ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
