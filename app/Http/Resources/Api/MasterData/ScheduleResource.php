<?php

namespace App\Http\Resources\Api\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
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
            'user_id' => $this->user_id,
            'employee_name' => $this->whenLoaded('user', function () {
                return $this->user->name;
            }),
            'shift_in_date' => $this->shift_in_date,
            'shift_out_date' => $this->shift_out_date,
            'shift_in' => $this->shift_in,
            'shift_out' => $this->shift_out,
            'remarks' => $this->remarks,
            'is_approved' => $this->is_approved,
            'is_validated' => $this->is_validated,
            'approved_by' => $this->approved_by,
            'validated_by' => $this->validated_by,
        ];
    }
}
