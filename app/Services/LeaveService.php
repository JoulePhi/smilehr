<?php

namespace App\Services;

use App\Models\Leave;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function getReportsData($page, $perPage, $search, $sortBy, $sortOrder)
    {
        $query = Leave::query();
        $query->select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as periode"),
            'user_id',
            DB::raw("SUM(CASE WHEN type = 'leave' THEN 1 ELSE 0 END) as total_leave"),
            DB::raw("SUM(CASE WHEN type = 'sick' THEN 1 ELSE 0 END) as total_sick"),
            DB::raw("SUM(CASE WHEN type = 'permission' THEN 1 ELSE 0 END) as total_permission")
        );

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        $query->groupBy('periode', 'user_id');

        $query->orderBy($sortBy, $sortOrder);

        if ($page && $perPage) {
            $leaves = $query->with(['user', 'user.branch', 'user.department', 'user.position'])->paginate($perPage, ['*'], 'page', $page);
        } else {
            $leaves = $query->with(['user', 'user.branch', 'user.department', 'user.position'])->get();
        }
        return $leaves;
    }
}
