<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Attendance::with('user', 'user.branch', 'user.department', 'user.position', 'user.costCenter')->get();
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
            'Tanggal/Waktu Masuk',
            'Tanggal/Waktu Keluar',
        ];
    }

    public function map($attendance): array
    {
        if ($attendance->date_in != null) {
            $attendance->date_in =  new Carbon($attendance->date_in);
        }
        if ($attendance->date_out != null) {
            $attendance->date_out =  new Carbon($attendance->date_out);
        }
        if ($attendance->time_in != null) {
            $attendance->time_in =  new Carbon($attendance->time_in);
        }
        if ($attendance->time_out != null) {
            $attendance->time_out =  new Carbon($attendance->time_out);
        }

        $formattedDateIn = $attendance->date_in ? $attendance->date_in->format('D, Y-m-d') : '';
        $formattedDateOut = $attendance->date_out ? $attendance->date_out->format('D, Y-m-d') : '';
        $formattedTimeIn = $attendance->time_in ? $attendance->time_in->format('H:i:s') : '';
        $formattedTimeOut = $attendance->time_out ? $attendance->time_out->format('H:i:s') : '';
        return [
            $attendance->id,
            $attendance->user?->name,
            $attendance->user?->branch?->name,
            $attendance->user?->department?->name,
            $attendance->user?->position?->name,
            $attendance->user?->costCenter?->name,
            $formattedDateIn . ' ' . $formattedTimeIn,
            $formattedDateOut . ' ' . $formattedTimeOut,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
