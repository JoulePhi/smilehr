<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreCostCenterRequest;
use App\Http\Requests\Api\MasterData\UpdateCostCenterRequest;
use App\Http\Resources\Api\MasterData\CostCenterResource;
use App\Models\CostCenter;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CostCenterController extends Controller
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

        $query = CostCenter::query();
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $query->orderBy($sortBy, $sortOrder);
        $branches = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Cost centers retrieved successfully.',
            'data' => CostCenterResource::collection($branches->items()),
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
    public function store(StoreCostCenterRequest $request)
    {
        try {
            DB::beginTransaction();
            $costCenter = CostCenter::create([
                'company_id' => $this->tenantService->getCompanyId(),
                'name' => $request->input('name'),
            ]);
            DB::commit();
            Log::info('Cost center created', ['cost_center_id' => $costCenter->id, 'name' => $costCenter->name]);
            return response()->json([
                'success' => true,
                'message' => 'Cost center created successfully.',
                'data' => new CostCenterResource($costCenter)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cost center creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create cost center.',
                'data' => null
            ], 500);
        }
    }


    public function search(Request $request)
    {
        try {
            $query = $request->get('query', '');
            $costCenters = CostCenter::where('name', 'like', "%$query%")
                ->where('company_id', $this->tenantService->getCompanyId())
                ->limit(5)
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Cost centers retrieved successfully.',
                'data' => CostCenterResource::collection($costCenters)
            ]);
        } catch (\Exception $e) {
            Log::error('Cost center search failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cost centers.',
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
    public function update(UpdateCostCenterRequest $request, CostCenter $costCenter)
    {
        try {
            DB::beginTransaction();
            $costCenter->update([
                'name' => $request->input('name', $costCenter->name),
            ]);
            DB::commit();
            Log::info('Cost center updated', ['cost_center_id' => $costCenter->id, 'name' => $costCenter->name]);
            return response()->json([
                'success' => true,
                'message' => 'Cost center updated successfully.',
                'data' => new CostCenterResource($costCenter)
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cost center update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cost center.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CostCenter $costCenter)
    {
        try {
            DB::beginTransaction();
            $costCenter->delete();
            DB::commit();
            Log::info('Cost center deleted', ['cost_center_id' => $costCenter->id, 'name' => $costCenter->name]);
            return response()->json([
                'success' => true,
                'message' => 'Cost center deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cost center deletion failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cost center.',
                'data' => null
            ], 500);
        }
    }
}
