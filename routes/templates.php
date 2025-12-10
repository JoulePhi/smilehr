<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('templates')->group(function () {
        Route::prefix('salaries')->group(function () {
            Route::get('/download', [\App\Http\Controllers\Api\MasterData\SalaryController::class, 'downloadTemplate'])
                ->name('api.template.salaries.download');
            Route::post('/import', [\App\Http\Controllers\Api\MasterData\SalaryController::class, 'importFinancials'])
                ->name('api.salaries.import');
        });

        Route::prefix('schedules')->group(function () {
            Route::get('/download', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'downloadTemplate'])
                ->name('api.template.schedules.download');
            Route::post('/import', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'importSchedules'])
                ->name('api.schedules.import');
            Route::get('/export', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'exportSchedules'])
                ->name('api.schedules.export');
            Route::get('/print', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'printSchedules'])
                ->name('api.schedules.print');
        });

        Route::prefix('overtimes')->group(function () {
            Route::get('/download', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'downloadTemplate'])
                ->name('api.template.overtimes.download');
            Route::post('/import', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'importOvertimes'])
                ->name('api.overtimes.import');
            Route::get('/export', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'exportOvertimes'])
                ->name('api.overtimes.export');
            Route::get('/print', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'printOvertimes'])
                ->name('api.overtimes.print');
        });

        Route::prefix('leaves')->group(function () {
            Route::get('/download', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'downloadTemplate'])
                ->name('api.template.leaves.download');
            Route::post('/import', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'importLeaves'])
                ->name('api.leaves.import');
            Route::get('/export', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'exportLeaves'])
                ->name('api.leaves.export');
            Route::get('/print', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'printLeaves'])
                ->name('api.leaves.print');
        });

        Route::prefix('debts')->group(function () {
            Route::get('/download', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'downloadTemplate'])
                ->name('api.template.debts.download');
            Route::post('/import', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'importDebts'])
                ->name('api.debts.import');
            Route::get('/export', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'exportDebts'])
                ->name('api.debts.export');
            Route::get('/print', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'printDebts'])
                ->name('api.debts.print');
        });

        Route::prefix('reimbursements')->group(function () {
            Route::get('/download', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'downloadTemplate'])
                ->name('api.template.reimbursements.download');
            Route::post('/import', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'importReimbursements'])
                ->name('api.reimbursements.import');
            Route::get('/export', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'exportReimbursements'])
                ->name('api.reimbursements.export');
            Route::get('/print', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'printReimbursements'])
                ->name('api.reimbursements.print');
        });

        Route::prefix('branches')->group(function () {
            Route::get('/export', [\App\Http\Controllers\Api\MasterData\BranchController::class, 'exportBranches'])
                ->name('api.branches.export');
            Route::get('/print', [\App\Http\Controllers\Api\MasterData\BranchController::class, 'printBranches'])
                ->name('api.branches.print');
        });

        Route::prefix('employees')->group(function () {
            Route::get('/export', [\App\Http\Controllers\Api\MasterData\EmployeeController::class, 'exportEmployees'])
                ->name('api.employees.export');
            Route::get('/print', [\App\Http\Controllers\Api\MasterData\EmployeeController::class, 'printEmployees'])
                ->name('api.employees.print');
        });
        Route::prefix('leaves-report')->group(function () {
            Route::get('/export', [\App\Http\Controllers\Api\Reports\LeaveController::class, 'exportLeaveReports'])
                ->name('api.leaves.export');
            Route::get('/print', [\App\Http\Controllers\Api\Reports\LeaveController::class, 'printLeavesReport'])
                ->name('api.leaves.print');
        });
        Route::prefix('overtimes-report')->group(function () {
            Route::get('/export', [\App\Http\Controllers\Api\Reports\OvertimeController::class, 'exportOvertimeReports'])
                ->name('api.overtimes.export');
            Route::get('/print', [\App\Http\Controllers\Api\Reports\OvertimeController::class, 'printOvertimesReport'])
                ->name('api.overtimes.print');
        });

        Route::prefix('debts-report')->group(function () {
            Route::get('/export', [\App\Http\Controllers\Api\Reports\DebtController::class, 'exportDebtReports'])
                ->name('api.debts.export');
            Route::get('/print', [\App\Http\Controllers\Api\Reports\DebtController::class, 'printDebtReport'])
                ->name('api.debts.print');
        });
    });
});
