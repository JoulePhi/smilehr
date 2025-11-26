<?php

namespace App\Traits;

use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    protected int $companyId;
    protected TenantService $tenantService;


    public function __construct()
    {
        $this->tenantService = app()->make(TenantService::class);
        $this->companyId = $this->tenantService->getCompanyId();
    }

    protected static function bootBelongsToCompany()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $builder->where('company_id', $this->companyId);
        });
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
