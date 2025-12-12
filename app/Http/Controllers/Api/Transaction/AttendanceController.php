<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Transaction\StoreAttendanceRequest;
use App\Http\Requests\Api\Transaction\UpdateAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Resources\Api\Transaction\AttendanceResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
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

        $query = Attendance::query()->with('user');
        if ($search) {
            $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        $query->orderBy($sortBy, $sortOrder);
        $attendances = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Attendances retrieved successfully.',
            'data' => AttendanceResource::collection($attendances->items()),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
                'from' => $attendances->firstItem(),
                'to' => $attendances->lastItem(),
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
    public function store(StoreAttendanceRequest $request)
    {
        try {
            $dataToInsert = collect($request->all())->map(function ($row) {
                return [
                    'user_id' => $row['employee_id'],
                    'date_in' => $row['date_in'],
                    'date_out' => $row['date_out'],
                    'time_in' => $row['time_in'],
                    'time_out' => $row['time_out'],
                    'remarks' => $row['remarks'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            DB::beginTransaction();
            foreach (array_chunk($dataToInsert, 500) as $chunk) {
                Attendance::insert($chunk);
            }
            DB::commit();
            Log::info('Attendance created', ['created_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Attendance created successfully.',
                'data' => AttendanceResource::collection(Attendance::whereIn('user_id', collect($dataToInsert)->pluck('user_id'))->get()),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance creation failed', ['error' => $e->getMessage(), 'created_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load('user');
        return response()->json([
            'success' => true,
            'message' => 'Attendance retrieved successfully.',
            'data' => new AttendanceResource($attendance),
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
    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        try {
            DB::beginTransaction();
            $attendance->update($request->validated());
            DB::commit();
            Log::info('Attendance updated', ['attendance_id' => $attendance->id, 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully.',
                'data' => new AttendanceResource($attendance),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance update failed', ['error' => $e->getMessage(), 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function exportAttendances()
    {
        return Excel::download(new AttendanceExport, 'attendances.xlsx');
    }

    public function printAttendances()
    {
        $attendances =  Attendance::with('user')->get();

        $pdf = Pdf::loadView('exports.attendances-pdf', [
            'attendances' => $attendances
        ]);

        return $pdf->download('attendances.pdf');
    }
}
