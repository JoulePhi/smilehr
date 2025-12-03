<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;

class UsersTemplateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::with('financial')->select('id', 'name', 'nik', 'email')->employee()->get();
    }

    public function headings(): array
    {
        return [
            'ID System (JANGAN DIUBAH)', // A: Critical for system
            'Nama Karyawan',             // B: Reference
            'NIK',                       // C: Reference
            'Gaji Pokok',                // D
            'Iuran Rutin',               // E
            'Tunjangan Tetap',           // F
            'Tunjangan Lainnya',         // G
            'Tunjangan Harian',          // H
            'BPJS JHT (Karyawan %)',     // I
            'BPJS Kes (Karyawan %)',     // J
            'BPJS JP (Karyawan %)',      // K
            'Lainnya (Karyawan %)',      // L
            'BPJS JHT (Perusahaan %)',   // M
            'BPJS Kes (Perusahaan %)',   // N
            'BPJS JP (Perusahaan %)',    // O
            'Lainnya (Perusahaan %)',    // P
            'BPJS JKM (%)',              // Q
            'BPJS JKK (%)',              // R
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->nik,
            $user->financial->basic_salary ?? '', // Gaji Pokok
            $user->financial->routine_contribution ?? '', // Iuran Rutin
            $user->financial->fixed_allowance ?? '', // Tunjangan Tetap
            $user->financial->other_allowance ?? '', // Tunjangan Lainnya
            $user->financial->daily_allowance ?? '', // Tunjangan Harian
            $user->financial->bpjs_jht_employee ?? '', // BPJS JHT User
            $user->financial->bpjs_kes_employee ?? '', // BPJS Kes User
            $user->financial->bpjs_jp_employee ?? '', // BPJS JP User
            $user->financial->other_employee ?? '', // Lainnya User
            $user->financial->bpjs_jht_company ?? '', // BPJS JHT Comp
            $user->financial->bpjs_kes_company ?? '', // BPJS Kes Comp
            $user->financial->bpjs_jp_company ?? '', // BPJS JP Comp
            $user->financial->other_company ?? '', // Lainnya Comp
            $user->financial->bpjs_jkm ?? '', // JKM
            $user->financial->bpjs_jkk ?? '', // JKK
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
