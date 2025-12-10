<?php

namespace App\Services\Report;

use App\Models\Debt;
use App\Models\Leave;
use Illuminate\Support\Facades\DB;

class DebtService
{
    public function getReportsData($page, $perPage, $search, $sortBy, $sortOrder)
    {
        $query = Debt::query();
        $query->select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as periode"),
            'user_id',
            DB::raw("SUM(amount) as total_debt_amount"),
        );

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        $query->groupBy('periode', 'user_id');

        $query->orderBy($sortBy, $sortOrder);

        if ($page && $perPage) {
            $debts = $query->with(['user', 'user.branch', 'user.department', 'user.position'])->paginate($perPage, ['*'], 'page', $page);
        } else {
            $debts = $query->with(['user', 'user.branch', 'user.department', 'user.position'])->get();
        }
        return $debts;
    }
}
