<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'cost_center_id', 'id');
    }
}
