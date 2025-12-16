<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Exports\SchedulesExport;
use App\Exports\SchedulesTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreScheduleRequest;
use App\Http\Requests\Api\MasterData\UpdateScheduleRequest;
use App\Http\Resources\Api\MasterData\ScheduleResource;
use App\Imports\SchedulesImport;
use App\Models\Schedule;
use App\Services\TenantService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{

    public function __construct(
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

        $query = Schedule::query()->with('user');
        if ($search) {
            $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        $query->orderBy($sortBy, $sortOrder);
        $schedules = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Schedules retrieved successfully.',
            'data' => ScheduleResource::collection($schedules->items()),
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'total' => $schedules->total(),
                'from' => $schedules->firstItem(),
                'to' => $schedules->lastItem(),
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
    public function store(StoreScheduleRequest $request)
    {
        try {
            $dataToInsert = collect($request->all())->map(function ($row) {
                // if schedule for user_id and shift_in_date already exists, skip to next
                if (Schedule::where('user_id', $row['employee_id'])->where('shift_in_date', $row['shift_in_date'])->exists()) {
                    return null;
                }
                return [
                    'user_id' => $row['employee_id'],
                    'shift_in_date' => $row['shift_in_date'],
                    'shift_in' => $row['shift_in'],
                    'shift_out_date' => $row['shift_out_date'],
                    'shift_out' => $row['shift_out'],
                    'remarks' => $row['remarks'] ?? '',
                    'is_approved' => $row['is_approved'] ?? 0,
                    'is_validated' => $row['is_validated'] ?? 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            })->toArray();
            DB::beginTransaction();
            foreach (array_chunk($dataToInsert, 500) as $chunk) {
                // Skip null entries
                $chunk = array_filter($chunk);
                if (!empty($chunk))
                    Schedule::insert($chunk);
            }
            DB::commit();
            Log::info('Schedule created', ['created_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully.',
                'data' => ScheduleResource::collection(Schedule::whereIn('user_id', collect($dataToInsert)->pluck('user_id'))->get()),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule creation failed', ['error' => $e->getMessage(), 'created_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create schedule.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load('user');
        return response()->json([
            'success' => true,
            'message' => 'Schedule retrieved successfully.',
            'data' => new ScheduleResource($schedule),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        try {
            DB::beginTransaction();
            $schedule->update($request->validated());
            DB::commit();
            Log::info('Schedule updated', ['schedule_id' => $schedule->id, 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully.',
                'data' => new ScheduleResource($schedule),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule update failed', ['error' => $e->getMessage(), 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update schedule.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        try {
            DB::beginTransaction();
            $schedule->delete();
            DB::commit();
            Log::info('Schedule deleted', ['schedule_id' => $schedule->id, 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule deletion failed', ['error' => $e->getMessage(), 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new SchedulesTemplateExport, 'schedules_template.xlsx');
    }
    public function exportSchedules()
    {
        return Excel::download(new SchedulesExport, 'schedules.xlsx');
    }
    public function importSchedules(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {
            Excel::import(new SchedulesImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Schedules imported successfully.',
                'data' => null
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            Log::error('Schedule import validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed during import.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function printSchedules()
    {
        $schedules =  Schedule::with('user')->get();

        $pdf = Pdf::loadView('exports.schedules-pdf', [
            'schedules' => $schedules
        ]);

        return $pdf->download('schedules.pdf');
    }
}
