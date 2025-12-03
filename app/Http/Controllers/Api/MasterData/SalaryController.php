<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Exports\UsersTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreSalaryRequest;
use App\Http\Requests\Api\MasterData\UpdateSalaryRequest;
use App\Http\Resources\Api\MasterData\SalaryResource;
use App\Imports\FinancialsImport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\HttpCache\Store;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSalaryRequest $request)
    {
        try {
            DB::beginTransaction();
            $salariesData = $request->input('salaries', []);
            $createdSalaries = [];
            foreach ($salariesData as $salaryData) {
                if (empty($salaryData['employee_id'])) {
                    Log::warning('Skipping salary creation due to missing employeeId', ['salaryData' => $salaryData]);
                    continue;
                }

                $employee = User::find($salaryData['employee_id']);
                if (!$employee) {
                    Log::error('Employee not found for salary creation', ['employee_id' => $salaryData['employee_id']]);
                    continue;
                }
                Log::info('Salary Data', ['salaryData' => $salaryData]);
                $upsertData = [
                    'basic_salary' => $salaryData['basic_salary'],
                    'routine_dues' => $salaryData['regular_dues'] ?? 0,
                    'fixed_allowance' => $salaryData['fixed_allowance'] ?? 0,
                    'other_allowance' => $salaryData['other_allowance'] ?? 0,
                    'daily_allowance' => $salaryData['daily_allowance'] ?? 0,
                    'bpjs_jht_user' => $salaryData['bpjs_jht_user'] ?? 0,
                    'bpjs_health_user' => $salaryData['bpjs_health_user'] ?? 0,
                    'bpjs_jp_user' => $salaryData['bpjs_jp_user'] ?? 0,
                    'others_user' => $salaryData['others_user'] ?? 0,
                    'bpjs_jht_company' => $salaryData['bpjs_jht_company'] ?? 0,
                    'bpjs_health_company' => $salaryData['bpjs_health_company'] ?? 0,
                    'bpjs_jp_company' => $salaryData['bpjs_jp_company'] ?? 0,
                    'others_company' => $salaryData['others_company'] ?? 0,
                    'bpjs_jkm' => $salaryData['bpjs_jkm'] ?? 0,
                    'bpjs_jkk' => $salaryData['bpjs_jkk'] ?? 0,
                ];


                $employee->financial()->updateOrCreate(
                    ['user_id' => $employee->id],
                    $upsertData
                );

                $createdSalaries[] = $employee->financial;
            }


            DB::commit();


            Log::info('Salary created', ['salary_count' => count($createdSalaries)]);

            return response()->json([
                'success' => true,
                'message' => 'Salaries created successfully. (' . count($createdSalaries) . ' records)',
                'data' => SalaryResource::collection($createdSalaries)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Branch creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create branch.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function downloadTemplate()
    {
        return Excel::download(new UsersTemplateExport, 'financial_update_template.xlsx');
    }

    public function importFinancials(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {
            Excel::import(new FinancialsImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Financial data updated successfully.',
                'data' => null
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            Log::error('Financial import validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed during import.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSalaryRequest $request, User $user)
    {
        try {
            Log::info('Updating salary', ['user' => $user]);
            DB::beginTransaction();
            $salaryData = $request->validated();
            $user->financial()->update([
                'basic_salary' => $salaryData['basic_salary'],
                'routine_dues' => $salaryData['routine_dues'],
                'fixed_allowance' => $salaryData['fixed_allowance'],
                'other_allowance' => $salaryData['other_allowance'],
                'daily_allowance' => $salaryData['daily_allowance'],
                'hourly_wages_based_on' => $salaryData['hourly_wages_based_on'],
                'hourly_deduction_based_on' => $salaryData['hourly_deduction_based_on'],
                'overtime_need_approval' => $salaryData['overtime_need_approval'],
                'overtime_calculation_method' => $salaryData['overtime_calculation_mode'],
                'weekday_pattern' => $salaryData['weekday_pattern'] ?? null,
                'overtime_multiplier' => $salaryData['overtime_multiplier'] ?? null,
                'special_overtime_multiplier' => $salaryData['special_overtime_multiplier'] ?? null,
                'daily_late_deduction_amount' => $salaryData['daily_late_deductions'],
                'daily_overtime_incentive' => $salaryData['daily_overtime_incentive'],
                'daily_leave_balance_incentive' => $salaryData['daily_leave_balance_incentive'],

                'bpjs_jht_user_percent' => $salaryData['bpjs_jht_user_percent'] ?? 0,
                'bpjs_health_user_percent' => $salaryData['bpjs_health_user_percent'] ?? 0,
                'bpjs_jp_user_percent' => $salaryData['bpjs_jp_user_percent'] ?? 0,
                'others_user_percent' => $salaryData['others_user_percent'] ?? 0,
                'bpjs_jht_company_percent' => $salaryData['bpjs_jht_company_percent'] ?? 0,
                'bpjs_health_company_percent' => $salaryData['bpjs_health_company_percent'] ?? 0,
                'bpjs_jp_company_percent' => $salaryData['bpjs_jp_company_percent'] ?? 0,
                'others_company_percent' => $salaryData['others_company_percent'] ?? 0,
                'bpjs_jkm_percent' => $salaryData['bpjs_jkm_percent'] ?? 0,
                'bpjs_jkk_percent' => $salaryData['bpjs_jkk_percent'] ?? 0,
                'total_salary' => $salaryData['total_salary'] ?? 0,
            ]);


            DB::commit();
            Log::info('Salary updated', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Salary updated successfully.',
                'data' => new SalaryResource($user->financial)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Salary update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update salary.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            $user->financial()->delete();

            DB::commit();

            Log::info('Salary deleted', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Salary deleted successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Salary deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete salary.',
                'data' => null
            ], 500);
        }
    }
}
