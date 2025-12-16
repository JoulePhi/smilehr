<?php

namespace App\Http\Resources\Api\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AttendanceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $scheduleData = new \App\Http\Resources\Api\MasterData\ScheduleResource($this);
        $attendanceData = null;

        if ($this->att_id) {
            $attObject = (object) [
                'id'         => $this->att_id,
                'user_id'    => $this->att_user_id,
                'date_in'    => $this->att_date_in,
                'date_out'   => $this->att_date_out,
                'time_in'    => $this->att_time_in,
                'time_out'   => $this->att_time_out,
                'lat_in'     => $this->att_lat_in,
                'lng_in'     => $this->att_lng_in,
                'lat_out'    => $this->att_lat_out,
                'lng_out'    => $this->att_lng_out,
                'photo_in'   => $this->att_photo_in,
                'photo_out'  => $this->att_photo_out,
                'remarks'    => $this->att_remarks,
                'created_at' => $this->att_created_at ? Carbon::parse($this->att_created_at) : null,
                'updated_at' => $this->att_updated_at ? Carbon::parse($this->att_updated_at) : null,
                'user'       => null,
            ];

            $attendanceData = new \App\Http\Resources\Api\Transaction\AttendanceResource($attObject);
        }
        $overtimeData = null;
        if ($this->ot_id) {
            $otObject = (object) [
                'id'             => $this->ot_id,
                'user_id'        => $this->ot_user_id,
                'shift_in_date'  => $this->ot_shift_in_date,
                'shift_out_date' => $this->ot_shift_out_date,
                'shift_in'       => $this->ot_shift_in,
                'shift_out'      => $this->ot_shift_out,
                'remarks'        => $this->ot_remarks,
                'is_approved'    => $this->ot_is_approved,
                'is_validated'   => $this->ot_is_validated,
                'is_special'     => $this->ot_is_special,
                'approved_by'    => $this->ot_approved_by,
                'validated_by'   => $this->ot_validated_by,
                'user'           => null,
            ];
            $overtimeData = new \App\Http\Resources\Api\MasterData\OvertimeResource($otObject);
        }
        $lateMinutes = 0;
        $overtimeMinutes = 0;
        $status = $this->att_id ? 'Present' : 'Absent';

        if ($this->att_id) {
            if ($this->shift_in && $this->att_time_in) {
                $shiftIn = Carbon::parse($this->shift_in_date . ' ' . $this->shift_in);
                $clockIn = Carbon::parse($this->att_date_in . ' ' . $this->att_time_in);
                if ($clockIn->gt($shiftIn)) {
                    $lateMinutes = $clockIn->diffInMinutes($shiftIn);
                    $status = 'Late';
                }
            }
            if ($this->shift_out && $this->att_time_out) {
                $shiftOut = Carbon::parse($this->shift_out_date . ' ' . $this->shift_out);
                $clockOut = Carbon::parse($this->att_date_out . ' ' . $this->att_time_out);
                if ($clockOut->gt($shiftOut)) {
                    $overtimeMinutes = $clockOut->diffInMinutes($shiftOut);
                }
            }
        }
        return [
            'schedule' => $scheduleData,
            'attendance' => $attendanceData,
            'overtime' => $overtimeData,
            'metrics' => [
                'status'           => $status,
                'late_minutes'     => abs((int) $lateMinutes),
                'overtime_minutes' => abs((int) $overtimeMinutes),
            ]
        ];
    }
}
