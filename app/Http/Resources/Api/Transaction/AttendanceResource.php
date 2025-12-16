<?php

namespace App\Http\Resources\Api\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'employee_id' => $this->user_id,
            'employee_name' => $this->user != null && $this->whenLoaded('user') ? $this->user->name : null,
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'time_in' =>  $this->time_in,
            'time_out' =>  $this->time_out,
            'lat_in' => $this->lat_in != null ? (float) $this->lat_in : null,
            'lng_in' => $this->lng_in != null ? (float) $this->lng_in : null,
            'lat_out' => $this->lat_out,
            'lng_out' => $this->lng_out,
            'photo_in' => $this->photo_in != null ? url('storage/' . $this->photo_in) : null,
            'photo_out' => $this->photo_out != null ? url('storage/' . $this->photo_out) : null,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at != null ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at != null ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
