<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'is_visit',
        'date_in',
        'date_out',
        'time_in',
        'time_out',
        'lat_in',
        'lng_in',
        'lat_out',
        'lng_out',
        'photo_in',
        'photo_out',
        'remarks',
    ];

    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
