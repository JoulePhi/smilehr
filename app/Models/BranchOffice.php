<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class BranchOffice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'npwp',
        'longitude',
        'latitude',
        'radius',
        'phone_number',
        'work_hour_in_weekday',
        'work_hour_out_weekday',
        'work_hour_in_weekend',
        'work_hour_out_weekend',
    ];

    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
        'radius' => 'float',
    ];
}
