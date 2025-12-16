<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function getReportsData($page, $perPage, $search, $sortBy, $sortOrder)
    {
        $query = Attendance::query()
            ->selectRaw("DATE_FORMAT(attendances.date_in, '%Y-%m') as periode")
            ->selectRaw('attendances.user_id')
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->selectRaw('users.name as user_name')
            ->leftJoin('schedules', function ($join) {
                $join->on('attendances.user_id', '=', 'schedules.user_id')
                    ->on('attendances.date_in', '=', 'schedules.shift_in_date');
            })

            ->selectRaw("COUNT(CASE 
        WHEN attendances.time_in IS NOT NULL 
        AND attendances.time_out IS NOT NULL 
        AND schedules.id IS NOT NULL 
        THEN 1 END) as total_worked_days")
            ->selectRaw("SUM(CASE WHEN attendances.is_visit = 1 THEN 1 ELSE 0 END) as total_visit")

            ->selectRaw("SUM(GREATEST(0, TIMESTAMPDIFF(MINUTE, schedules.shift_in, attendances.time_in))) as total_late_minutes")
            ->selectRaw("SUM(GREATEST(0, TIMESTAMPDIFF(MINUTE, attendances.time_out, schedules.shift_out))) as total_early_minutes")
            ->selectRaw("SUM(GREATEST(0, TIMESTAMPDIFF(MINUTE, schedules.shift_out, attendances.time_out))) as total_overtime_minutes")
            ->selectRaw("COUNT(CASE 
            WHEN TIMESTAMPDIFF(MINUTE, schedules.shift_in, attendances.time_in) > 0 
            OR TIMESTAMPDIFF(MINUTE, schedules.shift_out, attendances.time_out) > 0 
            THEN 1 END) as total_invalid_count");

        if ($search) {
            $query->where('users.name', 'like', "%$search%");
        }

        $query->groupBy('periode', 'attendances.user_id', 'users.name');
        $query->orderBy($sortBy, $sortOrder);

        if ($page && $perPage) {
            $attendances = $query->with(['user', 'user.branch', 'user.department', 'user.position'])->paginate($perPage, ['*'], 'page', $page);
        } else {
            $attendances = $query->with(['user', 'user.branch', 'user.department', 'user.position'])->get();
        }
        return $attendances;
    }

    public function getReportsDetail($employee_id, $period)
    {
        $date = Carbon::createFromFormat('Y-m', $period);
        $year = $date->year;
        $month = $date->month;

        $query = Schedule::query()
            ->select('schedules.*')
            ->addSelect([
                'attendances.id as att_id',
                'attendances.user_id as att_user_id',
                'attendances.is_visit as att_is_visit',
                'attendances.date_in as att_date_in',
                'attendances.date_out as att_date_out',
                'attendances.time_in as att_time_in',
                'attendances.time_out as att_time_out',
                'attendances.lat_in as att_lat_in',
                'attendances.lng_in as att_lng_in',
                'attendances.lat_out as att_lat_out',
                'attendances.lng_out as att_lng_out',
                'attendances.photo_in as att_photo_in',
                'attendances.photo_out as att_photo_out',
                'attendances.remarks as att_remarks',
                'attendances.created_at as att_created_at',
                'attendances.updated_at as att_updated_at',
            ])

            ->addSelect([
                'overtimes.id as ot_id',
                'overtimes.user_id as ot_user_id',
                'overtimes.shift_in_date as ot_shift_in_date',
                'overtimes.shift_out_date as ot_shift_out_date',
                'overtimes.shift_in as ot_shift_in',
                'overtimes.shift_out as ot_shift_out',
                'overtimes.remarks as ot_remarks',
                'overtimes.is_approved as ot_is_approved',
                'overtimes.is_validated as ot_is_validated',
                'overtimes.is_special as ot_is_special',
                'overtimes.approved_by as ot_approved_by',
                'overtimes.validated_by as ot_validated_by',
            ])


            ->leftJoin('attendances', function ($join) use ($employee_id) {
                $join->on('schedules.user_id', '=', 'attendances.user_id')
                    ->on('schedules.shift_in_date', '=', 'attendances.date_in');
            })
            ->leftJoin('overtimes', function ($join) use ($employee_id) {
                $join->on('schedules.user_id', '=', 'overtimes.user_id')
                    ->on('schedules.shift_in_date', '=', 'overtimes.shift_in_date');
            })
            ->where('schedules.user_id', $employee_id)
            ->whereYear('schedules.shift_in_date', $year)
            ->whereMonth('schedules.shift_in_date', $month)
            ->orderBy('schedules.shift_in_date', 'asc');
        return $query->get();
    }
}
