<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update master data salaries');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'basic_salary' => ['required', 'numeric'],
            'total_salary' => ['required', 'numeric'],
            'routine_dues' => ['required', 'numeric'],
            'fixed_allowance' => ['required', 'numeric'],
            'other_allowance' => ['required', 'numeric'],
            'daily_allowance' => ['required', 'numeric'],

            'hourly_wages_based_on' => ['required', 'string'],
            'total_hourly_wages' => ['required', 'numeric'],
            'overtime_need_approval' => ['required', 'numeric'],
            'overtime_calculation_mode' => ['required', 'string'],

            'weekday_pattern' => ['nullable', 'string'],
            'overtime_multiplier' => ['nullable', 'numeric'],
            'special_overtime_multiplier' => ['nullable', 'numeric'],

            'hourly_deduction_based_on' => ['required', 'string'],
            'total_hourly_deduction' => ['required', 'numeric'],
            'daily_late_deductions' => ['required', 'numeric'],
            'daily_overtime_incentive' => ['required', 'numeric'],
            'daily_leave_balance_incentive' => ['required', 'numeric'],

            'bpjs_jht_user_percent' => ['nullable', 'numeric'],
            'bpjs_health_user_percent' => ['nullable', 'numeric'],
            'bpjs_jp_user_percent' => ['nullable', 'numeric'],
            'others_user_percent' => ['nullable', 'numeric'],

            'bpjs_jht_company_percent' => ['nullable', 'numeric'],
            'bpjs_health_company_percent' => ['nullable', 'numeric'],
            'bpjs_jp_company_percent' => ['nullable', 'numeric'],
            'others_company_percent' => ['nullable', 'numeric'],

            'bpjs_jkm_percent' => ['nullable', 'numeric'],
            'bpjs_jkk_percent' => ['nullable', 'numeric'],
        ];
    }
}
