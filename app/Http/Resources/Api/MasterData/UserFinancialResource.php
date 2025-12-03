<?php

namespace App\Http\Resources\Api\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFinancialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'npwp' => $this->npwp,
            'tax_type' => $this->tax_type,
            'tax_deduction_amount' => $this->tax_deduction_amount !== null ? (int) $this->tax_deduction_amount : null,
            'tax_allowance_percent' => $this->tax_allowance_percent !== null ? (int) $this->tax_allowance_percent : null,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'bpjs_tk_number' => $this->bpjs_tk_number,
            'bpjs_kes_number' => $this->bpjs_kes_number,
            'basic_salary' =>  $this->basic_salary !== null ? (float) $this->basic_salary : null,
            'routine_dues' => $this->routine_dues !== null ? (float) $this->routine_dues : null,
            'fixed_allowance' => $this->fixed_allowance !== null ? (float) $this->fixed_allowance : null,
            'other_allowance' => $this->other_allowance !== null ? (float) $this->other_allowance : null,
            'daily_allowance' => $this->daily_allowance !== null ? (float) $this->daily_allowance : null,
            'insurance_by_emmployee' => $this->insurance_by_employee !== null ? (float) $this->insurance_by_employee : null,
            'bpjs_jht_percent' => $this->bpjs_jht_percent !== null ? (float) $this->bpjs_jht_percent : null,
            'bpjs_kesehatan_percent' => $this->bpjs_kesehatan_percent !== null ? (float) $this->bpjs_kesehatan_percent : null,
            'bpjs_jp_percent' => $this->bpjs_jp_percent !== null ? (float) $this->bpjs_jp_percent : null,
            'bpjs_jkm_percent' => $this->bpjs_jkm_percent !== null ? (float) $this->bpjs_jkm_percent : null,
            'bpjs_jkk_percent' => $this->bpjs_jkk_percent !== null ? (float) $this->bpjs_jkk_percent : null,
        ];
    }
}
