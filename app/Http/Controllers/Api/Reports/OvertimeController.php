<?php

namespace App\Http\Controllers\Api\Reports;

use App\Exports\Reports\OvertimesExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Reports\OvertimeResource;
use App\Services\OvertimeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
    public function __construct(
        private OvertimeService $overtimeService
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

        $overtimes = $this->overtimeService->getReportsData($page, $perPage, $search, $sortBy, $sortOrder);


        return response()->json([
            'success' => true,
            'message' => 'Overtime report retrieved successfully.',
            'data'    => OvertimeResource::collection($overtimes),
            'meta'    => [
                'current_page' => $overtimes->currentPage(),
                'last_page'    => $overtimes->lastPage(),
                'per_page'     => $overtimes->perPage(),
                'total'        => $overtimes->total(),
                'from'         => $overtimes->firstItem(),
                'to'           => $overtimes->lastItem(),
            ]
        ]);
    }

    public function exportOvertimeReports()
    {
        return Excel::download(new OvertimesExport, 'overtimes.xlsx');
    }

    public function printOvertimesReport()
    {
        $overtimes =  $this->overtimeService->getReportsData(null, null, null, 'periode', 'desc');

        $pdf = Pdf::loadView('exports.overtimes-report-pdf', [
            'overtimes' => $overtimes
        ]);

        return $pdf->download('overtimes.pdf');
    }
}
