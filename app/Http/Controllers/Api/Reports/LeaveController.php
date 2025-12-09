<?php

namespace App\Http\Controllers\Api\Reports;

use App\Exports\Reports\LeavesExport;
use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use App\Http\Resources\Api\Reports\LeaveResource;
use App\Services\Report\LeaveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LeaveController extends Controller
{

    public function __construct(
        private LeaveService $leaveService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'periode');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = min(max($perPage, 1), 100);

        $leaves = $this->leaveService->getReportsData($page, $perPage, $search, $sortBy, $sortOrder);


        return response()->json([
            'success' => true,
            'message' => 'Leave report retrieved successfully.',
            'data'    => LeaveResource::collection($leaves),
            'meta'    => [
                'current_page' => $leaves->currentPage(),
                'last_page'    => $leaves->lastPage(),
                'per_page'     => $leaves->perPage(),
                'total'        => $leaves->total(),
                'from'         => $leaves->firstItem(),
                'to'           => $leaves->lastItem(),
            ]
        ]);
    }

    public function exportLeaveReports()
    {
        return Excel::download(new LeavesExport, 'leaves.xlsx');
    }

    public function printLeavesReport()
    {
        $leaves =  $this->leaveService->getReportsData(null, null, null, 'periode', 'desc');

        $pdf = Pdf::loadView('exports.leaves-report-pdf', [
            'leaves' => $leaves
        ]);

        return $pdf->download('leaves.pdf');
    }
}
