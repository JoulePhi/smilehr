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
        'routine_dues',
        'fixed_allowance',
        'other_allowance',
        'daily_allowance',
        'insurance_by_employee',
        'bpjs_jht_percent',
        'bpjs_kesehatan_percent',
        'bpjs_jp_percent',
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
