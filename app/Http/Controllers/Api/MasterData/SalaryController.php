<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreSalaryRequest;
use App\Http\Requests\Api\MasterData\UpdateSalaryRequest;
use App\Http\Resources\Api\MasterData\SalaryResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSalaryRequest $request)
    {
        try {
            DB::beginTransaction();

            $salariesData = $request->input('salaries', []);
            $updatedSalaries = [];
            foreach ($salariesData as $salaryData) {
                if (empty($salaryData['employeeId'])) {
                    Log::warning('Skipping salary creation due to missing employeeId', ['salaryData' => $salaryData]);
                    continue;
                }

                $employee = User::find($salaryData['employeeId']);
                if (!$employee) {
                    Log::warning('Employee not found for salary creation', ['employeeId' => $salaryData['employeeId']]);
                    continue;
                }

                $upsertData = [
                    'basic_salary' => $salaryData['basicSalary'],
                    'routine_dues' => $salaryData['regularDues'] ?? 0,
                    'fixed_allowance' => $salaryData['fixedAllowance'] ?? 0,
                    'other_allowance' => $salaryData['otherAllowance'] ?? 0,
                    'daily_allowance' => $salaryData['dailyAllowance'] ?? 0,
                    'bpjs_jht_user' => $salaryData['bpjsJhtUser'] ?? 0,
                    'bpjs_health_user' => $salaryData['bpjsHealthUser'] ?? 0,
                    'bpjs_jp_user' => $salaryData['bpjsJpUser'] ?? 0,
                    'others_user' => $salaryData['othersUser'] ?? 0,
                    'bpjs_jht_company' => $salaryData['bpjsJhtCompany'] ?? 0,
                    'bpjs_health_company' => $salaryData['bpjsHealthCompany'] ?? 0,
                    'bpjs_jp_company' => $salaryData['bpjsJpCompany'] ?? 0,
                    'others_company' => $salaryData['othersCompany'] ?? 0,
                    'bpjs_jkm' => $salaryData['bpjsJkm'] ?? 0,
                    'bpjs_jkk' => $salaryData['bpjsJkk'] ?? 0,
                ];


                $employee->financial()->updateOrCreate(
                    ['user_id' => $employee->id],
                    $upsertData
                );

                $updatedSalaries[] = $employee->financial;
            }

            DB::commit();
            Log::info('Salary updated', ['salary_count' => count($updatedSalaries)]);

            return response()->json([
                'success' => true,
                'message' => 'Salary updated successfully.',
                'data' => SalaryResource::collection($updatedSalaries)
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
