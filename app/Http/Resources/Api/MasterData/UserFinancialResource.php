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
            'total_salary' =>  $this->total_salary !== null ? (float) $this->total_salary : null,
            'routine_dues' => $this->routine_dues !== null ? (float) $this->routine_dues : null,
            'fixed_allowance' => $this->fixed_allowance !== null ? (float) $this->fixed_allowance : null,
            'other_allowance' => $this->other_allowance !== null ? (float) $this->other_allowance : null,
            'daily_allowance' => $this->daily_allowance !== null ? (float) $this->daily_allowance : null,
            'insurance_by_emmployee' => $this->insurance_by_employee !== null ? (float) $this->insurance_by_employee : null,
            'bpjs_jht_user_percent' => $this->bpjs_jht_user_percent !== null ? (float) $this->bpjs_jht_user_percent : null,
            'bpjs_health_user_percent' => $this->bpjs_health_user_percent !== null ? (float) $this->bpjs_health_user_percent : null,
            'bpjs_jp_user_percent' => $this->bpjs_jp_user_percent !== null ? (float) $this->bpjs_jp_user_percent : null,
            'others_user_percent' => $this->others_user_percent !== null ? (float) $this->others_user_percent : null,
            'bpjs_jht_company_percent' => $this->bpjs_jht_company_percent !== null ? (float) $this->bpjs_jht_company_percent : null,
            'bpjs_health_company_percent' => $this->bpjs_health_company_percent !== null ? (float) $this->bpjs_health_company_percent : null,
            'bpjs_jp_company_percent' => $this->bpjs_jp_company_percent !== null ? (float) $this->bpjs_jp_company_percent : null,
            'others_company_percent' => $this->others_company_percent !== null ? (float) $this->others_company_percent : null,
            'bpjs_jkm_percent' => $this->bpjs_jkm_percent !== null ? (float) $this->bpjs_jkm_percent : null,
            'bpjs_jkk_percent' => $this->bpjs_jkk_percent !== null ? (float) $this->bpjs_jkk_percent : null,

            'hourly_wages_based_on' => $this->hourly_wages_based_on,
            'overtime_need_approval' => $this->overtime_need_approval,
            'overtime_calculation_method' => $this->overtime_calculation_method,
            'weekday_pattern' => $this->weekday_pattern,
            'overtime_multiplier' => $this->overtime_multiplier !== null ? (float) $this->overtime_multiplier : null,
            'special_overtime_multiplier' => $this->special_overtime_multiplier !== null ? (float) $this->special_overtime_multiplier : null,
            'hourly_deduction_based_on' => $this->hourly_deduction_based_on,
            'daily_late_deduction_amount' => $this->daily_late_deduction_amount !== null ? (float) $this->daily_late_deduction_amount : null,
            'daily_overtime_incentive' => $this->daily_overtime_incentive !== null ? (float) $this->daily_overtime_incentive : null,
            'daily_leave_balance_incentive' => $this->daily_leave_balance_incentive !== null ? (float) $this->daily_leave_balance_incentive : null
        ];
    }
}
