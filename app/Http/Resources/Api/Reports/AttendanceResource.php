<?php

namespace App\Http\Resources\Api\Reports;

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
            'periode'             => $this->periode,
            'employee_id'             => $this->user_id,
            'employee_name'           => $this->user_name,
            'total_worked_days'   => (int) $this->total_worked_days,
            'total_visits'         => (int) $this->total_visit,
            'total_invalids'      => (int) $this->total_invalid_count,
            'total_lates'          => (int) $this->total_late_minutes,
            'total_leave_earlys'   => (int) $this->total_early_minutes,
            'total_overtimes'      => (int) $this->total_overtime_minutes,
        ];
    }
}
