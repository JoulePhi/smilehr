<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_office_id', 'id');
    }
}
