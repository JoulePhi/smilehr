<?php

namespace App\Http\Resources\Api\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeResource extends JsonResource
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
            'overtime_total'      => (int) $this->total_overtimes,
            'total_hours'       => (int) $this->total_hours,
            'overtime_fee'        => $this->whenLoaded('user', function () {
                if ($this->user->financial) {
                    return app(\App\Services\Report\OvertimeService::class)->calculateFee(
                        $this->user->financial->overtime_calculation_method,
                        $this->user->financial->hourly_wages_based_on,
                        $this->user->financial->basic_salary,
                        $this->user->financial->fixed_allowance,
                        $this->total_hours,
                        $this->user->financial->overtime_multiplier
                    );
                }
                return 0;
            }),
        ];
    }
}
