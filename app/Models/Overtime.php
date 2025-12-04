<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    protected $fillable = [
        'user_id',
        'shift_in_date',
        'shift_out_date',
        'shift_in',
        'shift_out',
        'remarks',
        'is_approved',
        'is_validated',
        'is_special',
        'approved_by',
        'validated_by',
    ];

    /**
     * Get the user that owns the Schedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
