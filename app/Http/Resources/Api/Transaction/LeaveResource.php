<?php

namespace App\Http\Resources\Api\Transaction;

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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'employee_name' => $this->whenLoaded('user', function () {
                return $this->user->name;
            }),
            'date' => $this->date,
            'type' => $this->type,
            'remarks' => $this->remarks,
            'is_approved' => $this->is_approved,
            'is_validated' => $this->is_validated,
            'attachment' => $this->attachment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
