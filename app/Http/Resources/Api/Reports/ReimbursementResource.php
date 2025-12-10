<?php

namespace App\Http\Resources\Api\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReimbursementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'periode'          => $this->periode,
            'employee_id'      => $this->user_id,
            'employee_name'    => $this->whenLoaded('user') ? $this->user->name : null,
            'branch_name'           => $this->whenLoaded('user') && $this->user->branch ? $this->user->branch->name : null,
            'department_name'       => $this->whenLoaded('user') && $this->user->department ? $this->user->department->name : null,
            'position_name'         => $this->whenLoaded('user') && $this->user->position ? $this->user->position->name : null,
            'amount'      => (int) $this->total_reimburse_amount,
        ];
    }
}
