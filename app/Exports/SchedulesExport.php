<?php

namespace App\Exports;

use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SchedulesExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Schedule::with('user', 'user.branch', 'user.department', 'user.position', 'user.costCenter')->get();
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
            'Masuk',
            'Keluar',
            'Keterangan',
            'Approved'
        ];
    }

    public function map($schedule): array
    {
        if ($schedule->shift_in_date != null) {
            $schedule->shift_in_date =  new Carbon($schedule->shift_in_date);
        }
        if ($schedule->shift_out_date != null) {
            $schedule->shift_out_date =  new Carbon($schedule->shift_out_date);
        }

        if ($schedule->shift_in != null) {
            $schedule->shift_in = Carbon::createFromFormat('H:i:s', $schedule->shift_in);
        }
        if ($schedule->shift_out != null) {
            $schedule->shift_out = Carbon::createFromFormat('H:i:s', $schedule->shift_out);
        }



        $formattedShiftInDate = $schedule->shift_in_date ? $schedule->shift_in_date->format('D, Y-m-d') : '';
        $formattedShiftOutDate = $schedule->shift_out_date ? $schedule->shift_out_date->format('D, Y-m-d') : '';

        $formattedShiftIn = $schedule->shift_in ? $schedule->shift_in->format('H:i') : '';
        $formattedShiftOut = $schedule->shift_out ? $schedule->shift_out->format('H:i') : '';



        return [
            $schedule->id,
            $schedule->user?->name,
            $schedule->user?->branch?->name,
            $schedule->user?->department?->name,
            $schedule->user?->position?->name,
            $schedule->user?->costCenter?->name,
            $formattedShiftInDate . ' ' . $formattedShiftIn,
            $formattedShiftOutDate . ' ' . $formattedShiftOut,
            $schedule->remarks,
            $schedule->is_approved ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
