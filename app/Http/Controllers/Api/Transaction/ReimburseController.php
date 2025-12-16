<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Exports\ReimburseExport;
use App\Exports\ReimburseTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Transaction\StoreReimburseRequest;
use App\Http\Requests\Api\Transaction\UpdateReimburseRequest;
use App\Http\Resources\Api\Transaction\ReimburseResource;
use App\Imports\ReimburseImport;
use App\Models\Reimburse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReimburseController extends Controller
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

        $query = Reimburse::query()->with('user');
        if ($search) {
            $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        $query->orderBy($sortBy, $sortOrder);
        $reimbursements = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Debts retrieved successfully.',
            'data' => ReimburseResource::collection($reimbursements->items()),
            'meta' => [
                'current_page' => $reimbursements->currentPage(),
                'last_page' => $reimbursements->lastPage(),
                'per_page' => $reimbursements->perPage(),
                'total' => $reimbursements->total(),
                'from' => $reimbursements->firstItem(),
                'to' => $reimbursements->lastItem(),
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
    public function store(StoreReimburseRequest $request)
    {
        try {
            $dataToInsert = collect($request->all())->map(function ($row) {
                return [
                    'user_id' => $row['employee_id'],
                    'date' => $row['date'],
                    'amount' => $row['amount'],
                    'remarks' => $row['remarks'] ?? '',
                    'is_approved' => $row['is_approved'] ?? 0,
                    'is_paid' => $row['is_paid'] ?? 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            })->toArray();
            DB::beginTransaction();
            foreach (array_chunk($dataToInsert, 500) as $chunk) {
                Reimburse::insert($chunk);
            }
            DB::commit();
            Log::info('Reimburse created', ['created_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Reimburse created successfully.',
                'data' => ReimburseResource::collection(Reimburse::whereIn('user_id', collect($dataToInsert)->pluck('user_id'))->get()),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reimburse creation failed', ['error' => $e->getMessage(), 'created_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reimburse.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reimburse $reimburse)
    {
        $reimburse->load('user');
        return response()->json([
            'success' => true,
            'message' => 'Reimburse retrieved successfully.',
            'data' => new ReimburseResource($reimburse),
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
    public function update(UpdateReimburseRequest $request, Reimburse $reimburse)
    {
        try {
            DB::beginTransaction();
            $reimburse->update($request->validated());
            DB::commit();
            Log::info('Reimburse updated', ['reimburse_id' => $reimburse->id, 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Reimburse updated successfully.',
                'data' => new ReimburseResource($reimburse),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reimburse update failed', ['error' => $e->getMessage(), 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reimburse.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reimburse $reimburse)
    {
        try {
            DB::beginTransaction();
            $reimburse->delete();
            DB::commit();
            Log::info('Reimburse deleted', ['reimburse_id' => $reimburse->id, 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Reimburse deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reimburse deletion failed', ['error' => $e->getMessage(), 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reimburse.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ReimburseTemplateExport, 'reimburse_template.xlsx');
    }

    public function exportReimbursements()
    {
        return Excel::download(new ReimburseExport, 'reimburses.xlsx');
    }

    public function importReimbursements(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {
            Excel::import(new ReimburseImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Reimburse imported successfully.',
                'data' => null
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            Log::error('Reimburse import validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed during import.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function printReimbursements()
    {
        $reimburses =  Reimburse::with('user')->get();

        $pdf = Pdf::loadView('exports.reimburses-pdf', [
            'reimburses' => $reimburses
        ]);

        return $pdf->download('reimburses.pdf');
    }
}
