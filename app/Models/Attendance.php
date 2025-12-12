<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'time_out',
        'location_lat',
        'location_lng',
        'photo_in',
        'photo_out',
        'remarks',
    ];
}
