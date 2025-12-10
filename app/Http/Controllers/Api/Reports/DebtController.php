<?php

namespace App\Http\Controllers\Api\Reports;

use App\Exports\Reports\DebtsExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Reports\DebtResource;
use App\Services\Report\DebtService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DebtController extends Controller
{

    public function __construct(
        private DebtService $debtService
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

        $debts = $this->debtService->getReportsData($page, $perPage, $search, $sortBy, $sortOrder);


        return response()->json([
            'success' => true,
            'message' => 'Debt report retrieved successfully.',
            'data'    => DebtResource::collection($debts),
            'meta'    => [
                'current_page' => $debts->currentPage(),
                'last_page'    => $debts->lastPage(),
                'per_page'     => $debts->perPage(),
                'total'        => $debts->total(),
                'from'         => $debts->firstItem(),
                'to'           => $debts->lastItem(),
            ]
        ]);
    }
    public function exportDebtReports()
    {
        return Excel::download(new DebtsExport, 'debts.xlsx');
    }

    public function printDebtReport()
    {
        $debts =  $this->debtService->getReportsData(null, null, null, 'periode', 'desc');

        $pdf = Pdf::loadView('exports.debts-report-pdf', [
            'debts' => $debts
        ]);

        return $pdf->download('debts.pdf');
    }
}
