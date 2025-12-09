<?php

namespace App\Http\Resources\Api\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
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
            'leave_total'      => (int) $this->total_leave,
            'sick_total'       => (int) $this->total_sick,
            'permission_total' => (int) $this->total_permission,
        ];
    }
}
