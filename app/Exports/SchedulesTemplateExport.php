<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SchedulesTemplateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::select('id', 'name', 'nik', 'email')->employee()->get();
    }

    public function headings(): array
    {
        return [
            'ID System (JANGAN DIUBAH)', // A: Critical for system
            'Nama Karyawan',             // B: Reference
            'Shift In Date (dd/mm/yyyy)',   // C
            'Shift In Time (h:mm)',       // D
            'Shift Out Date (dd/mm/yyyy)', // E
            'Shift Out Time (h:mm)',      // F
            'Remark',                     // G
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            '', // Shift In Date
            '', // Shift In Time
            '', // Shift Out Date
            '', // Shift Out Time
            '', // Remark
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_DATE_TIME3,
        ];
    }
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A' => ['fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EFEFEF']
            ]],
        ];
    }
}
