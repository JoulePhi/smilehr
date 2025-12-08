<?php

namespace App\Imports;

use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeaveImport implements ToCollection, WithHeadingRow, WithValidation, WithMapping
{
    public function map($row): array
    {
        Log::info('Mapping row', ['row' => $row]);
        $type = strtolower($row['tipe_izin_sakitizincuti'] ?? '');
        if ($type == 'izin') {
            $type = 'permission';
        } else if ($type == 'sakit') {
            $type = 'sick';
        } else if ($type == 'cuti') {
            $type = 'leave';
        } else {
            $type = 'other';
        }
        return [
            'user_id'        => $row['id_system_jangan_diubah'] ?? $row['id'] ?? null,
            'type' => $type,
            'date'  => $this->transformDate($row['date_ddmmyyyy'] ?? $row['date_mmddyyyy']),
            'remark'         => $row['remark'] ?? null,
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

                Leave::create([
                    'user_id'       => $row['user_id'],
                    'type'    => $row['type'],
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
            'type' => 'required|in:permission,sick,leave',
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

    private function transformTime($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('H:i:s');
            }
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
