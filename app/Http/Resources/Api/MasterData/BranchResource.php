<?php

namespace App\Http\Resources\Api\MasterData;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
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
            'company_id' => $this->company_id,
            'name' => $this->name,
            'address' => $this->address,
            'npwp' => $this->npwp,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'radius' => $this->radius,
            'phone_number' => $this->phone_number,
            'work_hour_in_weekday' => $this->work_hour_in_weekday,
            'work_hour_out_weekday' => $this->work_hour_out_weekday,
            'work_hour_in_weekend' => $this->work_hour_in_weekend,
            'work_hour_out_weekend' => $this->work_hour_out_weekend,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
