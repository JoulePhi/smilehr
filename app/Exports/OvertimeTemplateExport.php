<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OvertimeTemplateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        return User::select('id', 'name', 'nik', 'email')->employee()->get();
    }

    public function headings(): array
    {
        return [
            'ID System (JANGAN DIUBAH)', // A: Critical for system
            'Nama Karyawan',             // B: Reference
            'Tipe Overtime (Regular/Special)', // New Column
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
            '', // Tipe Overtime (Regular/Special)
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
