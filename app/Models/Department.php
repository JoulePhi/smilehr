<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'company_id',
        'name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id', 'id');
    }
}
