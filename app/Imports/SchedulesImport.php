<?php

namespace App\Imports;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SchedulesImport implements ToCollection, WithHeadingRow, WithValidation, WithMapping
{

    public function map($row): array
    {
        // 2. We normalize everything to standard keys and formats here
        return [
            'user_id'        => $row['id_system_jangan_diubah'] ?? $row['id'] ?? null,

            // Convert Excel Date (number or string) to Y-m-d
            'shift_in_date'  => $this->transformDate($row['shift_in_date_mmddyyyy'] ?? $row['shift_in_date_ddmmyyyy']),
            'shift_in_time'  => $this->transformTime($row['shift_in_time_hhmm'] ?? $row['shift_in_time_hmm']),

            'shift_out_date' => $this->transformDate($row['shift_out_date_mmddyyyy'] ?? $row['shift_out_date_ddmmyyyy']),
            'shift_out_time' => $this->transformTime($row['shift_out_time_hhmm'] ?? $row['shift_out_time_hmm']),

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
                // 4. Data is already clean, just save it
                if (!$row['user_id']) continue;

                Schedule::create([
                    'user_id'       => $row['user_id'],
                    'shift_in_date' => $row['shift_in_date'],
                    'shift_in'      => $row['shift_in_time'],
                    'shift_out_date' => $row['shift_out_date'],
                    'shift_out'     => $row['shift_out_time'],
                    'remarks'        => $row['remark'] ?? '',
                ]);
            }
        });
    }

    public function rules(): array
    {
        // 3. Validate the CLEANED data from map()
        return [
            'user_id'        => 'required|exists:users,id',
            // Now we can use standard date validation because map() fixed the format
            'shift_in_date'  => 'required|date_format:Y-m-d',
            'shift_in_time'  => 'required|date_format:H:i:s',
            'shift_out_date' => 'required|date_format:Y-m-d',
            'shift_out_time' => 'required|date_format:H:i:s',
            'remark'         => 'nullable|string',
        ];
    }

    /**
     * Helper to handle both Excel Numbers (46024) and Strings (02/01/2026)
     */
    private function transformDate($value)
    {
        if (empty($value)) return null;

        try {
            // If Excel passed a number (e.g., 46024)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            // If Excel passed a text string (e.g., "02/01/2026")
            // Adjust the format 'd/m/Y' to match your Excel file's text format
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null; // Invalid date
        }
    }

    private function transformTime($value)
    {
        if (empty($value)) return null;

        try {
            // Excel time is a fraction (e.g., 0.5 = 12:00)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('H:i:s');
            }
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
