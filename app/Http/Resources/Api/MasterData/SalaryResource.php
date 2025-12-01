<?php

namespace App\Http\Resources\Api\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee ? $this->employee->name : null,
            'basic_salary' => $this->basic_salary,
            'regular_dues' => $this->routine_dues,
            'fixed_allowance' => $this->fixed_allowance,
            'other_allowance' => $this->other_allowance,
            'daily_allowance' => $this->daily_allowance,
            'bpjs_jht_user' => $this->bpjs_jht_user,
            'bpjs_health_user' => $this->bpjs_health_user,
            'bpjs_jp_user' => $this->bpjs_jp_user,
            'others_user' => $this->others_user,
            'bpjs_jht_company' => $this->bpjs_jht_company,
            'bpjs_health_company' => $this->bpjs_health_company,
            'bpjs_jp_company' => $this->bpjs_jp_company,
            'others_company' => $this->others_company,
            'bpjs_jkm' => $this->bpjs_jkm,
            'bpjs_jkk' => $this->bpjs_jkk,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
