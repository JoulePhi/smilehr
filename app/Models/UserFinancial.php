<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFinancial extends Model
{
    protected $fillable = [
        'user_id',
        'npwp',
        'tax_type',
        'tax_deduction_amount',
        'tax_allowance_percent',
        'bank_name',
        'bank_account_number',
        'bpjs_tk_number',
        'bpjs_kes_number',
        'basic_salary',
        'total_salary',
        'routine_dues',
        'fixed_allowance',
        'other_allowance',
        'daily_allowance',
        'hourly_wages_based_on',
        'overtime_need_approval',
        'overtime_calculation_method',
        'weekday_pattern',
        'overtime_multiplier',
        'special_overtime_multiplier',
        'hourly_deduction_based_on',
        'daily_late_deduction_amount',
        'daily_overtime_incentive',
        'daily_leave_balance_incentive',
        'bpjs_jht_user_percent',
        'bpjs_health_user_percent',
        'bpjs_jp_user_percent',
        'others_user_percent',
        'bpjs_jht_company_percent',
        'bpjs_health_company_percent',
        'bpjs_jp_company_percent',
        'others_company_percent',
        'bpjs_jkm_percent',
        'bpjs_jkk_percent',
    ];


    /**
     * Get the user that owns the UserFinancial
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
