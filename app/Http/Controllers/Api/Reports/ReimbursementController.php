<?php

namespace App\Http\Controllers\Api\Reports;

use App\Exports\Reports\ReimbursesExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Reports\ReimbursementResource;
use App\Services\ReimbursementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReimbursementController extends Controller
{
    public function __construct(
        private ReimbursementService $reimbursementService
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

        $reimburses = $this->reimbursementService->getReportsData($page, $perPage, $search, $sortBy, $sortOrder);


        return response()->json([
            'success' => true,
            'message' => 'Reimburse report retrieved successfully.',
            'data'    => ReimbursementResource::collection($reimburses),
            'meta'    => [
                'current_page' => $reimburses->currentPage(),
                'last_page'    => $reimburses->lastPage(),
                'per_page'     => $reimburses->perPage(),
                'total'        => $reimburses->total(),
                'from'         => $reimburses->firstItem(),
                'to'           => $reimburses->lastItem(),
            ]
        ]);
    }
    public function exportReimburseReports()
    {
        return Excel::download(new ReimbursesExport, 'reimburses.xlsx');
    }

    public function printReimburseReport()
    {
        $reimburses =  $this->reimbursementService->getReportsData(null, null, null, 'periode', 'desc');

        $pdf = Pdf::loadView('exports.reimburses-report-pdf', [
            'reimburses' => $reimburses
        ]);

        return $pdf->download('reimburses.pdf');
    }
}
