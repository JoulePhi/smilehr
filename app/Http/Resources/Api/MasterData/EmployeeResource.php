<?php

namespace App\Http\Resources\Api\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'nik' => $this->nik,
            'phone' => $this->phone,
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),
            'position' => $this->whenLoaded('position', function () {
                return PositionResource::make($this->position);
            }),
            'department' => $this->whenLoaded('department', function () {
                return DepartmentResource::make($this->department);
            }),
            'branch' => $this->whenLoaded('branch', function () {
                return BranchResource::make($this->branch);
            }),
            'cost_center' => $this->whenLoaded('costCenter', function () {
                return CostCenterResource::make($this->costCenter);
            }),
            'financial' => $this->whenLoaded('financial', function () {
                return UserFinancialResource::make($this->financial);
            }),
            'profile_url' => $this->profile_url ? asset('storage/' . $this->profile_url) : null,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'gender' => $this->gender,
            'religion' => $this->religion,
            'address' => $this->address,
            'domicile_address' => $this->domicile_address,
            'last_education' => $this->last_education,
            'join_date' => $this->join_date,
            'employment_status' => $this->employment_status,
            'contract_start' => $this->contract_start,
            'contract_end' => $this->contract_end,
            'late_deduction' => $this->late_deduction,
            'late_tolerance' => $this->late_tolerance,
            'allow_remote_attendance' => $this->allow_remote_attendance,
            'allow_branch_hopping' => $this->allow_branch_hopping,
            'check_in_mode' => $this->check_in_mode,
            'id_card_number' => $this->id_card_number,


        ];
    }
}
