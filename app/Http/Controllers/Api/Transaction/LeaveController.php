<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Exports\LeaveExport;
use App\Exports\LeaveTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Transaction\StoreLeaveRequest;
use App\Http\Requests\Api\Transaction\UpdateLeaveRequest;
use App\Http\Resources\Api\Transaction\LeaveResource;
use App\Imports\LeaveImport;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LeaveController extends Controller
{
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

        $query = Leave::query()->with('user');
        if ($search) {
            $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        $query->orderBy($sortBy, $sortOrder);
        $leaves = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Leaves retrieved successfully.',
            'data' => LeaveResource::collection($leaves->items()),
            'meta' => [
                'current_page' => $leaves->currentPage(),
                'last_page' => $leaves->lastPage(),
                'per_page' => $leaves->perPage(),
                'total' => $leaves->total(),
                'from' => $leaves->firstItem(),
                'to' => $leaves->lastItem(),
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
    public function store(StoreLeaveRequest $request)
    {
        try {
            $dataToInsert = collect($request->all())->map(function ($row) {
                return [
                    'user_id' => $row['employee_id'],
                    'date' => $row['date'],
                    'type' => $row['type'],
                    'remarks' => $row['remarks'] ?? null,
                    'is_approved' => $row['is_approved'] ?? 0,
                    'is_validated' => $row['is_validated'] ?? 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            })->toArray();
            DB::beginTransaction();
            foreach (array_chunk($dataToInsert, 500) as $chunk) {
                Leave::insert($chunk);
            }
            DB::commit();
            Log::info('Leave created', ['created_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Leave created successfully.',
                'data' => LeaveResource::collection(Leave::whereIn('user_id', collect($dataToInsert)->pluck('user_id'))->get()),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave creation failed', ['error' => $e->getMessage(), 'created_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create leave.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        $leave->load('user');
        return response()->json([
            'success' => true,
            'message' => 'Leave retrieved successfully.',
            'data' => new LeaveResource($leave),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leave $leave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeaveRequest $request, Leave $leave)
    {
        try {
            DB::beginTransaction();
            $leave->update($request->validated());
            DB::commit();
            Log::info('Leave updated', ['leave_id' => $leave->id, 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Leave updated successfully.',
                'data' => new LeaveResource($leave),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave update failed', ['error' => $e->getMessage(), 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave)
    {
        try {
            DB::beginTransaction();
            $leave->delete();
            DB::commit();
            Log::info('Leave deleted', ['leave_id' => $leave->id, 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Leave deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave deletion failed', ['error' => $e->getMessage(), 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leave.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new LeaveTemplateExport, 'leave_template.xlsx');
    }

    public function exportLeaves()
    {
        return Excel::download(new LeaveExport, 'leaves.xlsx');
    }

    public function importLeaves(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {
            Excel::import(new LeaveImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Leave imported successfully.',
                'data' => null
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            Log::error('Leave import validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed during import.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function printLeaves()
    {
        $leaves =  Leave::with('user')->get();

        $pdf = Pdf::loadView('exports.leaves-pdf', [
            'leaves' => $leaves
        ]);

        return $pdf->download('leaves.pdf');
    }
}
