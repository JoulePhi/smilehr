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

class DebtTemplateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
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
            'Date (dd/mm/yyyy)',   // C
            'Jumlah (Desimal)', // New Column
            'Note',                     // G
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            '', // Tipe Izin (Sakit/Izin/Cuti)
            '', // Date
            '', // Remark
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_NUMBER_00,
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
