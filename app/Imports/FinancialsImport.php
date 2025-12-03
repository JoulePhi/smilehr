<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserFinancial;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FinancialsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $userId = $row['id_system_jangan_diubah'] ?? $row['id'] ?? null;

                if (!$userId) continue;
                UserFinancial::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        // Map Excel Slugs -> Database Columns
                        'basic_salary'        => $row['gaji_pokok'] ?? 0,
                        'routine_dues'        => $row['iuran_rutin'] ?? 0,
                        'fixed_allowance'     => $row['tunjangan_tetap'] ?? 0,
                        'other_allowance'     => $row['tunjangan_lainnya'] ?? 0,
                        'daily_allowance'     => $row['tunjangan_harian'] ?? 0,

                        // BPJS User
                        'bpjs_jht_user_percent'    => $row['bpjs_jht_karyawan_percent'] ?? 0,
                        'bpjs_health_user_percent' => $row['bpjs_kes_karyawan_percent'] ?? 0,
                        'bpjs_jp_user_percent'     => $row['bpjs_jp_karyawan_percent'] ?? 0,
                        'others_user_percent'      => $row['lainnya_karyawan_percent'] ?? 0,

                        // BPJS Company
                        'bpjs_jht_company_percent'    => $row['bpjs_jht_perusahaan_percent'] ?? 0,
                        'bpjs_health_company_percent' => $row['bpjs_kes_perusahaan_percent'] ?? 0,
                        'bpjs_jp_company_percent'     => $row['bpjs_jp_perusahaan_percent'] ?? 0,
                        'others_company_percent'      => $row['lainnya_perusahaan_percent'] ?? 0,

                        // Others
                        'bpjs_jkm_percent' => $row['bpjs_jkm_percent'] ?? 0,
                        'bpjs_jkk_percent' => $row['bpjs_jkk_percent'] ?? 0,
                    ]
                );
            }
        });
    }

    public function rules(): array
    {
        return [
            'id_system_jangan_diubah' => 'required|exists:users,id',
            'gaji_pokok' => 'required|numeric|min:0',
        ];
    }
}
