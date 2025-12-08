<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Exports\DebtExport;
use App\Exports\DebtTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Transaction\StoreDebtRequest;
use App\Http\Requests\Api\Transaction\UpdateDebtRequest;
use App\Http\Resources\Api\Transaction\DebtResource;
use App\Imports\DebtImport;
use App\Models\Debt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DebtController extends Controller
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

        $query = Debt::query()->with('user');
        if ($search) {
            $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        $query->orderBy($sortBy, $sortOrder);
        $leaves = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Debts retrieved successfully.',
            'data' => DebtResource::collection($leaves->items()),
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
    public function store(StoreDebtRequest $request)
    {
        try {
            $dataToInsert = collect($request->all())->map(function ($row) {
                return [
                    'user_id' => $row['employee_id'],
                    'date' => $row['date'],
                    'amount' => $row['amount'],
                    'remarks' => $row['remarks'] ?? null,
                    'is_approved' => $row['is_approved'] ?? 0,
                    'is_paid' => $row['is_paid'] ?? 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            })->toArray();
            DB::beginTransaction();
            foreach (array_chunk($dataToInsert, 500) as $chunk) {
                Debt::insert($chunk);
            }
            DB::commit();
            Log::info('Debt created', ['created_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Debt created successfully.',
                'data' => DebtResource::collection(Debt::whereIn('user_id', collect($dataToInsert)->pluck('user_id'))->get()),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Debt creation failed', ['error' => $e->getMessage(), 'created_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create debt.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Debt $debt)
    {
        $debt->load('user');
        return response()->json([
            'success' => true,
            'message' => 'Debt retrieved successfully.',
            'data' => new DebtResource($debt),
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
    public function update(UpdateDebtRequest $request, Debt $debt)
    {
        try {
            DB::beginTransaction();
            $debt->update($request->validated());
            DB::commit();
            Log::info('Debt updated', ['debt_id' => $debt->id, 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Debt updated successfully.',
                'data' => new DebtResource($debt),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Debt update failed', ['error' => $e->getMessage(), 'updated_by' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update debt.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Debt $debt)
    {
        try {
            DB::beginTransaction();
            $debt->delete();
            DB::commit();
            Log::info('Debt deleted', ['debt_id' => $debt->id, 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => true,
                'message' => 'Debt deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Debt deletion failed', ['error' => $e->getMessage(), 'deleted_by' => request()->user()->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete debt.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new DebtTemplateExport, 'debt_template.xlsx');
    }

    public function exportDebts()
    {
        return Excel::download(new DebtExport, 'debts.xlsx');
    }

    public function importDebts(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {
            Excel::import(new DebtImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Debt imported successfully.',
                'data' => null
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            Log::error('Debt import validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed during import.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function printDebts()
    {
        $debts =  Debt::with('user')->get();

        $pdf = Pdf::loadView('exports.debts-pdf', [
            'debts' => $debts
        ]);

        return $pdf->download('debts.pdf');
    }
}
