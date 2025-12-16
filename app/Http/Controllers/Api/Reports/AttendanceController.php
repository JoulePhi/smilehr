<?php

namespace App\Http\Controllers\Api\Reports;

use App\Exports\Reports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{


    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'periode');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = min(max($perPage, 1), 100);

        $attendances = $this->attendanceService->getReportsData($page, $perPage, $search, $sortBy, $sortOrder);

        return response()->json([
            'success' => true,
            'message' => 'Attendance report retrieved successfully.',
            'data'    => \App\Http\Resources\Api\Reports\AttendanceResource::collection($attendances),
            'meta'    => [
                'current_page' => $attendances->currentPage(),
                'last_page'    => $attendances->lastPage(),
                'per_page'     => $attendances->perPage(),
                'total'        => $attendances->total(),
                'from'         => $attendances->firstItem(),
                'to'           => $attendances->lastItem(),
            ]
        ]);
    }

    public function detail(Request $request, $employee_id, $period)
    {
        $details = $this->attendanceService->getReportsDetail($employee_id, $period);
        return response()->json([
            'success' => true,
            'message' => 'Attendance detail retrieved successfully.',
            'data'    => \App\Http\Resources\Api\Reports\AttendanceDetailResource::collection($details),
        ]);
    }

    public function exportAttendanceReports()
    {
        return Excel::download(new AttendanceExport, 'attendance_reports.xlsx');
    }

    public function printAttendanceReport()
    {
        $attendances =  $this->attendanceService->getReportsData(null, null, null, 'periode', 'desc');

        $pdf = Pdf::loadView('exports.attendances-report-pdf', [
            'attendances' => $attendances
        ]);

        return $pdf->download('attendances.pdf');
    }
}
