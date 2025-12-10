<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\Overtime;
use Illuminate\Support\Facades\DB;

class OvertimeService
{
    public function getReportsData($page, $perPage, $search, $sortBy, $sortOrder)
    {
        $query = Overtime::query()
            ->selectRaw("DATE_FORMAT(shift_in_date, '%Y-%m') as periode")
            ->selectRaw('user_id')
            ->selectRaw('COUNT(id) as total_overtimes')
            ->selectRaw('SUM(TIMESTAMPDIFF(HOUR, shift_in, shift_out)) as total_hours');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        $query->groupBy('periode', 'user_id');

        $query->orderBy($sortBy, $sortOrder);

        if ($page && $perPage) {
            $overtimes = $query->with(['user', 'user.branch', 'user.department', 'user.position', 'user.financial'])->paginate($perPage, ['*'], 'page', $page);
        } else {
            $overtimes = $query->with(['user', 'user.branch', 'user.department', 'user.position', 'user.financial'])->get();
        }
        return $overtimes;
    }

    public function calculateFee($method, $based_on, $basic_salary, $fixed_allowance, $total_hours, $overtime_multiplier)
    {
        if ($method == 'legal') {
            $multiplier = 1;
            if ($based_on == 'basic_salary') {
                $multiplier = ($basic_salary / 173);
            } elseif ($based_on == 'fixed_allowance') {
                $multiplier = ($fixed_allowance / 173);
            } else if ($based_on == 'total_salary') {
                $multiplier = (($basic_salary + $fixed_allowance) / 173);
            }
            $multiplier  = (int) $multiplier;
            return  $total_hours * $multiplier;
        } else {
            return (int) ($total_hours * $overtime_multiplier);
        }
    }
}
