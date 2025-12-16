<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Exports\OvertimeExport;
use App\Exports\OvertimeTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreOvertimeRequest;
use App\Http\Requests\Api\MasterData\UpdateOvertimeRequest;
use App\Http\Resources\Api\MasterData\OvertimeResource;
use App\Imports\OvertimeImport;
use App\Models\Overtime;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
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

        $query = Overtime::query()->with('user');
        if ($search) {
            $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        $query->orderBy($sortBy, $sortOrder);
        $overtimes = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Overtimes retrieved successfully.',
            'data' => OvertimeResource::collection($overtimes->items()),
            'meta' => [
                'current_page' => $overtimes->currentPage(),
                'last_page' => $overtimes->lastPage(),
                'per_page' => $overtimes->perPage(),
                'total' => $overtimes->total(),
                'from' => $overtimes->firstItem(),
                'to' => $overtimes->lastItem(),
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
    public function store(StoreOvertimeRequest $request)
    {
        try {
            $dataToInsert = collect($request->all())->map(function ($row) {
                return [
                    'user_id' => $row['employee_id'],
                    'shift_in_date' => $row['shift_in_date'],
                    'shift_in' => $row['shift_in'],
                    'shift_out_date' => $row['shift_out_date'],
                    'shift_out' => $row['shift_out'],
                    'remarks' => $row['remarks'] ?? '',
                    'is_special' => $row['is_special'] ?? 0,
                    'is_approved' => $row['is_approved'] ?? 0,
                    'is_validated' => $row['is_validated'] ?? 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            })->toArray();
            DB::beginTransaction();
            foreach (array_chunk($dataToInsert, 500) as $chunk) {
                Overtime::insert($chunk);
            }
            DB::commit();
            Log::info('Overtime created', ['created_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Overtime created successfully.',
                'data' => OvertimeResource::collection(Overtime::whereIn('user_id', collect($dataToInsert)->pluck('user_id'))->get()),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Overtime creation failed', ['error' => $e->getMessage(), 'created_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create overtime.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Overtime $overtime)
    {
        $overtime->load('user');
        return response()->json([
            'success' => true,
            'message' => 'Overtime retrieved successfully.',
            'data' => new OvertimeResource($overtime),
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
    public function update(UpdateOvertimeRequest $request, Overtime $overtime)
    {
        try {
            DB::beginTransaction();
            $overtime->update($request->validated());
            DB::commit();
            Log::info('Overtime updated', ['overtime_id' => $overtime->id, 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Overtime updated successfully.',
                'data' => new OvertimeResource($overtime),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Overtime update failed', ['error' => $e->getMessage(), 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update overtime.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Overtime $overtime)
    {
        try {
            DB::beginTransaction();
            $overtime->delete();
            DB::commit();
            Log::info('Overtime deleted', ['overtime_id' => $overtime->id, 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Overtime deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Overtime deletion failed', ['error' => $e->getMessage(), 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete overtime.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new OvertimeTemplateExport, 'overtime_template.xlsx');
    }
    public function exportOvertimes()
    {
        return Excel::download(new OvertimeExport, 'overtimes.xlsx');
    }
    public function importOvertimes(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {
            Excel::import(new OvertimeImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Overtime imported successfully.',
                'data' => null
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            Log::error('Overtime import validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed during import.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function printOvertimes()
    {
        $overtimes =  Overtime::with('user')->get();

        $pdf = Pdf::loadView('exports.overtimes-pdf', [
            'overtimes' => $overtimes
        ]);

        return $pdf->download('overtimes.pdf');
    }
}
