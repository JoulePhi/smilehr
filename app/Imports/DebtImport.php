<?php

namespace App\Imports;

use App\Models\Debt;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DebtImport implements ToCollection, WithHeadingRow, WithValidation, WithMapping
{
    public function map($row): array
    {

        return [
            'user_id'        => $row['id_system_jangan_diubah'] ?? $row['id'] ?? null,
            'amount'    => $row['jumlah_desimal'] ?? null,
            'date'  => $this->transformDate($row['date_ddmmyyyy'] ?? $row['date_mmddyyyy']),
            'remark'         => $row['note'] ?? null,
        ];
    }
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (!$row['user_id']) continue;

                Debt::create([
                    'user_id'       => $row['user_id'],
                    'amount'    => $row['amount'],
                    'date'  => $row['date'],
                    'remarks'        => $row['remark'] ?? '',
                ]);
            }
        });
    }

    public function rules(): array
    {
        return [
            'user_id'        => 'required|exists:users,id',
            'amount'    => 'required|numeric',
            'date'  => 'required|date_format:Y-m-d',
            'remark'         => 'nullable|string',
        ];
    }


    private function transformDate($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
