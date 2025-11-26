<?php

namespace App\Services;

class TenantService
{
    protected $companyId;

    public function setCompanyId($id)
    {
        $this->companyId = $id;
    }

    public function getCompanyId()
    {
        return $this->companyId;
    }
}
