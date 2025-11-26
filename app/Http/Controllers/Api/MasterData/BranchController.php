<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreBranchRequest;
use App\Http\Requests\Api\MasterData\UpdateBranchRequest;
use App\Http\Resources\Api\MasterData\BranchResource;
use App\Services\MasterData\BranchOfficeService;
use Illuminate\Http\{JsonResponse, Request};
use App\Models\BranchOffice;
use App\Services\TenantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{

    public function __construct(
        private BranchOfficeService $branchOfficeService,
        private TenantService $tenantService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $guard = $request->get('guard', 'web');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $perPage = min(max($perPage, 1), 100);

        $query = BranchOffice::query();
        if ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('address', 'like', "%$search%");
        }
        $query->orderBy($sortBy, $sortOrder);
        $branches = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Branch offices retrieved successfully.',
            'data' => BranchResource::collection($branches->items()),
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
    public function store(StoreBranchRequest $request)
    {
        try {
            DB::beginTransaction();
            $branch = BranchOffice::create([
                'company_id' => $this->tenantService->getCompanyId(),
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'npwp' => $request->input('npwp'),
                'longitude' => $request->input('longitude'),
                'latitude' => $request->input('latitude'),
                'radius' => $request->input('radius'),
                'phone_number' => $request->input('phone_number'),
                'work_hour_in_weekday' => $request->input('work_hour_in_weekday'),
                'work_hour_out_weekday' => $request->input('work_hour_out_weekday'),
                'work_hour_in_weekend' => $request->input('work_hour_in_weekend'),
                'work_hour_out_weekend' => $request->input('work_hour_out_weekend'),
            ]);


            DB::commit();


            Log::info('Branch created', ['branch_id' => $branch->id, 'name' => $branch->name]);

            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully.',
                'data' => new BranchResource($branch)
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
    public function show($id)
    {
        $branch = BranchOffice::find($id);

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Branch retrieved successfully.',
            'data' => new BranchResource($branch)
        ]);
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
    public function update(UpdateBranchRequest $request, BranchOffice $branch)
    {
        try {
            DB::beginTransaction();

            $branch->update($request->only([
                'name',
                'address',
                'npwp',
                'longitude',
                'latitude',
                'radius',
                'phone_number',
                'work_hour_in_weekday',
                'work_hour_out_weekday',
                'work_hour_in_weekend',
                'work_hour_out_weekend',
            ]));

            DB::commit();

            Log::info('Branch updated', ['branch_id' => $branch->id, 'name' => $branch->name]);

            return response()->json([
                'success' => true,
                'message' => 'Branch updated successfully.',
                'data' => new BranchResource($branch)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Branch update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update branch.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BranchOffice $branch)
    {
        try {
            DB::beginTransaction();

            $branch->delete();

            DB::commit();

            Log::info('Branch deleted', ['branch_id' => $branch->id, 'name' => $branch->name]);

            return response()->json([
                'success' => true,
                'message' => 'Branch deleted successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Branch deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete branch.',
                'data' => null
            ], 500);
        }
    }
}
