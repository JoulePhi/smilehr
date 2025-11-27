<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StorePositionRequest;
use App\Http\Requests\Api\MasterData\UpdatePositionRequest;
use App\Http\Resources\Api\MasterData\PositionResource;
use App\Models\Position;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\HttpCache\Store;

class PositionController extends Controller
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

        $query = Position::query();
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $query->orderBy($sortBy, $sortOrder);
        $positions = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Positions retrieved successfully.',
            'data' => PositionResource::collection($positions->items()),
            'meta' => [
                'current_page' => $positions->currentPage(),
                'last_page' => $positions->lastPage(),
                'per_page' => $positions->perPage(),
                'total' => $positions->total(),
                'from' => $positions->firstItem(),
                'to' => $positions->lastItem(),
            ]
        ]);
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('query', '');
            $positions = Position::where('name', 'like', "%$query%")
                ->where('company_id', $this->tenantService->getCompanyId())
                ->limit(5)
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Positions retrieved successfully.',
                'data' => PositionResource::collection($positions)
            ]);
        } catch (\Exception $e) {
            Log::error('Position search failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve positions.',
                'data' => null
            ], 500);
        }
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
    public function store(StorePositionRequest $request)
    {
        try {
            DB::beginTransaction();
            $position = Position::create([
                'company_id' => $this->tenantService->getCompanyId(),
                'name' => $request->input('name'),
            ]);
            DB::commit();
            Log::info('Position created', ['position_id' => $position->id, 'name' => $position->name]);
            return response()->json([
                'success' => true,
                'message' => 'Position created successfully.',
                'data' => new PositionResource($position)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create position.',
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
    public function update(UpdatePositionRequest $request, Position $position)
    {
        try {
            DB::beginTransaction();
            $position->update([
                'name' => $request->input('name'),
            ]);
            DB::commit();
            Log::info('Position updated', ['position_id' => $position->id, 'name' => $position->name]);
            return response()->json([
                'success' => true,
                'message' => 'Position updated successfully.',
                'data' => new PositionResource($position)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update position.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        try {
            DB::beginTransaction();
            $position->delete();
            DB::commit();
            Log::info('Position deleted', ['position_id' => $position->id, 'name' => $position->name]);
            return response()->json([
                'success' => true,
                'message' => 'Position deleted successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Position deletion failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete position.',
                'data' => null
            ], 500);
        }
    }
}
