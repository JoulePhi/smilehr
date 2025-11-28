<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreEmployeeRequest;
use App\Http\Requests\Api\MasterData\UpdateEmployeeRequest;
use App\Http\Resources\Api\MasterData\EmployeeResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserFinancial;
use App\Services\TenantService;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{

    public function __construct(
        private TenantService $tenantService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $guard = $request->get('guard', 'web');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $perPage = min(max($perPage, 1), 100);

        $query = User::query()->employee()->with(['position', 'department', 'branch', 'costCenter', 'company', 'financial']);
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('nik', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }
        $query->orderBy($sortBy, $sortOrder);
        $branches = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved successfully.',
            'data' => EmployeeResource::collection($branches->items()),
            'meta' => [
                'current_page' => $branches->currentPage(),
                'last_page' => $branches->lastPage(),
                'per_page' => $branches->perPage(),
                'total' => $branches->total(),
                'from' => $branches->firstItem(),
                'to' => $branches->lastItem(),
            ]
        ]);
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
    public function store(StoreEmployeeRequest $request,  UploadService $uploader)
    {
        try {
            DB::beginTransaction();

            $path = $uploader->upload($request->file('profile_image'));

            $employee = User::create([
                'company_id' => $this->tenantService->getCompanyId(),
                'branch_office_id' => $request->branch_office_id,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'cost_center_id' => $request->cost_center_id,
                'nik' => $request->nik,
                'name' => $request->name,
                'profile_url' => $path,
                'email' => $request->email,
                'phone' => $request->phone,
                'birth_date' => $request->birth_date,
                'birth_place' => $request->birth_place,
                'gender' => $request->gender,
                'religion' => $request->religion,

                'address' => $request->address,
                'domicile_address' => $request->domicile_address,
                'last_education' => $request->last_education,
                'id_card_number' => $request->id_card_number,

                'join_date' => $request->join_date,
                'employment_status' => $request->employment_status,
                'contract_start' => $request->contract_start,
                'contract_end' => $request->contract_end,

                'late_deduction' => $request->late_deduction,
                'late_tolerance' => $request->late_tolerance,
                'allow_remote_attendance' => $request->allow_remote_attendance,
                'allow_branch_hopping' => $request->allow_branch_hopping,
                'check_in_mode' => $request->check_in_mode,

                'password' => bcrypt('12345678'),
            ]);

            $employee->assignRole('Employee');

            $userFinance = UserFinancial::create([
                'user_id' => $employee->id,

                'npwp' => $request->npwp,
                'tax_type' => $request->tax_type,
                'tax_deduction_amount' => $request->tax_deduction_amount,
                'tax_allowance_percent' => $request->tax_allowance_percent,

                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,

                'bpjs_tk_number' => $request->bpjs_tk_number,
                'bpjs_kes_number' => $request->bpjs_kes_number,
            ]);
            $employee->load(['financial', 'position', 'department', 'branch', 'costCenter', 'company']);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully.',
                'data' => new EmployeeResource($employee),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $employee)
    {
        $employee->load(['position', 'department', 'branch', 'costCenter', 'company', 'financial']);
        return response()->json([
            'success' => true,
            'message' => 'Employee retrieved successfully.',
            'data' => new EmployeeResource($employee),
        ], 200);
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
    public function update(UpdateEmployeeRequest $request, User $employee, UploadService $uploader)
    {
        try {
            DB::beginTransaction();
            $profileUrl = $employee->profile_url;
            if ($request->hasFile('profile_image')) {
                $profileUrl = $uploader->upload($request->file('profile_image'));
            }
            $employee->update([
                'branch_office_id' => $request->branch_office_id ?? $employee->branch_office_id,
                'department_id' => $request->department_id ?? $employee->department_id,
                'position_id' => $request->position_id ?? $employee->position_id,
                'cost_center_id' => $request->cost_center_id ?? $employee->cost_center_id,

                'nik' => $request->nik ?? $employee->nik,
                'name' => $request->name ?? $employee->name,
                'profile_url' => $profileUrl, // updated or kept

                'email' => $request->email ?? $employee->email,
                'phone' => $request->phone ?? $employee->phone,

                'birth_date' => $request->birth_date ?? $employee->birth_date,
                'birth_place' => $request->birth_place ?? $employee->birth_place,
                'gender' => $request->gender ?? $employee->gender,
                'religion' => $request->religion ?? $employee->religion,

                'address' => $request->address ?? $employee->address,
                'domicile_address' => $request->domicile_address ?? $employee->domicile_address,
                'last_education' => $request->last_education ?? $employee->last_education,
                'id_card_number' => $request->id_card_number ?? $employee->id_card_number,

                'join_date' => $request->join_date ?? $employee->join_date,
                'employment_status' => $request->employment_status ?? $employee->employment_status,
                'contract_start' => $request->contract_start ?? $employee->contract_start,
                'contract_end' => $request->contract_end ?? $employee->contract_end,

                'late_deduction' => $request->late_deduction ?? $employee->late_deduction,
                'late_tolerance' => $request->late_tolerance ?? $employee->late_tolerance,

                'allow_remote_attendance' => $request->allow_remote_attendance ?? $employee->allow_remote_attendance,
                'allow_branch_hopping' => $request->allow_branch_hopping ?? $employee->allow_branch_hopping,
                'check_in_mode' => $request->check_in_mode ?? $employee->check_in_mode,
            ]);

            $financial = $employee->financial;

            if (!$financial) {
                $financial = UserFinancial::create([
                    'user_id' => $employee->id
                ]);
            }

            $financial->update([
                'npwp' => $request->npwp ?? $financial->npwp,
                'tax_type' => $request->tax_type ?? $financial->tax_type,
                'tax_deduction_amount' => $request->tax_deduction_amount ?? $financial->tax_deduction_amount,
                'tax_allowance_percent' => $request->tax_allowance_percent ?? $financial->tax_allowance_percent,

                'bank_name' => $request->bank_name ?? $financial->bank_name,
                'bank_account_number' => $request->bank_account_number ?? $financial->bank_account_number,

                'bpjs_tk_number' => $request->bpjs_tk_number ?? $financial->bpjs_tk_number,
                'bpjs_kes_number' => $request->bpjs_kes_number ?? $financial->bpjs_kes_number,
            ]);
            $employee->load(['position', 'department', 'branch', 'costCenter', 'company']);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully.',
                'data' => new EmployeeResource($employee),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employee)
    {
        try {
            DB::beginTransaction();
            $employee->financial()->delete();
            $employee->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully.',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
