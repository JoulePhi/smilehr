<?php

namespace App\Exports;

use App\Models\Reimburse;
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

class ReimburseExport implements FromCollection, WithHeadings, WithMapping,  ShouldAutoSize, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        return Reimburse::with('user', 'user.branch', 'user.department', 'user.position', 'user.costCenter', 'user.financial')->get();
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
            'Bank',
            'Nomor Rekening',
            'Tanggal',
            'Jumlah',
            'Keterangan',
            'Approved',
            'Paid'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function map($debt): array
    {
        if ($debt->date != null) {
            $debt->date =  new Carbon($debt->date);
        }
        $formattedDate = $debt->date ? $debt->date->format('D, Y-m-d') : '';
        return [
            $debt->id,
            $debt->user?->name,
            $debt->user?->branch?->name,
            $debt->user?->department?->name,
            $debt->user?->position?->name,
            $debt->user?->costCenter?->name,
            Str::upper($debt->user?->financial?->bank_name),
            $debt->user?->financial?->bank_account_number,
            $formattedDate,
            $debt->amount,
            $debt->remarks,
            $debt->is_approved ? 'Yes' : 'No',
            $debt->is_paid ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
