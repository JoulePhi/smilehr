<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'address',
        'email',
    ];


    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }
    public function branches(): HasMany
    {
        return $this->hasMany(BranchOffice::class, 'company_id', 'id');
    }
}
