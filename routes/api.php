<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::prefix('attendance')->group(function () {
    Route::post('/verify-id-card', [\App\Http\Controllers\Api\AttendanceController::class, 'verifyIdCard']);
    Route::post('/verify-pin', [\App\Http\Controllers\Api\AttendanceController::class, 'verifyPin']);
    Route::post('/verify-face', [\App\Http\Controllers\Api\AttendanceController::class, 'verifyFace']);
    Route::post('/check-in', [\App\Http\Controllers\Api\AttendanceController::class, 'checkIn']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/auth/profile', [\App\Http\Controllers\Api\AuthController::class, 'profile']);


    Route::prefix('master-data')->group(function () {
        Route::prefix('banners')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\BannerController::class, 'index'])
                ->middleware('permission:view master data home')
                ->name('api.banners.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\BannerController::class, 'store'])
                ->middleware('permission:create master data home')
                ->name('api.banners.store');

            Route::prefix('{banner}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\BannerController::class, 'show'])->name('api.banners.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\BannerController::class, 'update'])
                    ->middleware('permission:edit master data home')
                    ->name('api.banners.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\BannerController::class, 'destroy'])
                    ->middleware('permission:delete master data home')
                    ->name('api.banners.destroy');
            });
        });

        Route::prefix('branches')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\BranchController::class, 'index'])
                ->middleware('permission:view master data branches')
                ->name('api.branches.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\BranchController::class, 'store'])
                ->middleware('permission:create master data branches')
                ->name('api.branches.store');
            Route::get('/search', [\App\Http\Controllers\Api\MasterData\BranchController::class, 'search'])
                ->middleware('permission:view master data branches')
                ->name('api.branches.search');

            Route::prefix('{branch}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\BranchController::class, 'show'])
                    ->middleware('permission:view master data branches')
                    ->name('api.branches.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\BranchController::class, 'update'])
                    ->middleware('permission:update master data branches')
                    ->name('api.branches.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\BranchController::class, 'destroy'])
                    ->middleware('permission:delete master data branches')
                    ->name('api.branches.destroy');
            });
        });

        Route::prefix('departments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\DepartmentController::class, 'index'])
                ->middleware('permission:create master data employees')
                ->name('api.departments.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\DepartmentController::class, 'store'])
                ->middleware('permission:create master data employees')
                ->name('api.departments.store');
            Route::get('/search', [\App\Http\Controllers\Api\MasterData\DepartmentController::class, 'search'])
                ->middleware('permission:create master data employees')
                ->name('api.departments.search');

            Route::prefix('{department}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\DepartmentController::class, 'show'])
                    ->middleware('permission:create master data employees')
                    ->name('api.departments.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\DepartmentController::class, 'update'])
                    ->middleware('permission:create master data employees')
                    ->name('api.departments.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\DepartmentController::class, 'destroy'])
                    ->middleware('permission:create master data employees')
                    ->name('api.departments.destroy');
            });
        });

        Route::prefix('positions')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\PositionController::class, 'index'])
                ->middleware('permission:create master data employees')
                ->name('api.positions.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\PositionController::class, 'store'])
                ->middleware('permission:create master data employees')
                ->name('api.positions.store');
            Route::get('/search', [\App\Http\Controllers\Api\MasterData\PositionController::class, 'search'])
                ->middleware('permission:create master data employees')
                ->name('api.positions.search');

            Route::prefix('{position}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\PositionController::class, 'show'])
                    ->middleware('permission:create master data employees')
                    ->name('api.positions.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\PositionController::class, 'update'])
                    ->middleware('permission:create master data employees')
                    ->name('api.positions.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\PositionController::class, 'destroy'])
                    ->middleware('permission:create master data employees')
                    ->name('api.positions.destroy');
            });
        });

        Route::prefix('cost-centers')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\CostCenterController::class, 'index'])
                ->middleware('permission:create master data employees')
                ->name('api.cost-centers.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\CostCenterController::class, 'store'])
                ->middleware('permission:create master data employees')
                ->name('api.cost-centers.store');
            Route::get('/search', [\App\Http\Controllers\Api\MasterData\CostCenterController::class, 'search'])
                ->middleware('permission:create master data employees')
                ->name('api.cost-centers.search');

            Route::prefix('{costCenter}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\CostCenterController::class, 'show'])
                    ->middleware('permission:create master data employees')
                    ->name('api.cost-centers.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\CostCenterController::class, 'update'])
                    ->middleware('permission:create master data employees')
                    ->name('api.cost-centers.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\CostCenterController::class, 'destroy'])
                    ->middleware('permission:create master data employees')
                    ->name('api.cost-centers.destroy');
            });
        });

        Route::prefix('employees')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\EmployeeController::class, 'index'])
                ->middleware('permission:view master data employees')
                ->name('api.employees.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\EmployeeController::class, 'store'])
                ->middleware('permission:create master data employees')
                ->name('api.employees.store');

            Route::get('/search', [\App\Http\Controllers\Api\MasterData\EmployeeController::class, 'search'])
                ->middleware('permission:view master data employees')
                ->name('api.employees.search');

            Route::prefix('{employee}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\EmployeeController::class, 'show'])
                    ->middleware('permission:view master data employees')
                    ->name('api.employees.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\EmployeeController::class, 'update'])
                    ->middleware('permission:update master data employees')
                    ->name('api.employees.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\EmployeeController::class, 'destroy'])
                    ->middleware('permission:delete master data employees')
                    ->name('api.employees.destroy');
            });
        });

        Route::prefix('salaries')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\SalaryController::class, 'index'])
                ->middleware('permission:view master data salaries')
                ->name('api.salaries.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\SalaryController::class, 'store'])
                ->middleware('permission:create master data salaries')
                ->name('api.salaries.store');

            Route::prefix('{user}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\SalaryController::class, 'show'])
                    ->middleware('permission:view master data salaries')
                    ->name('api.salaries.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\SalaryController::class, 'update'])
                    ->middleware('permission:update master data salaries')
                    ->name('api.salaries.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\SalaryController::class, 'destroy'])
                    ->middleware('permission:delete master data salaries')
                    ->name('api.salaries.destroy');
            });
        });

        Route::prefix('schedules')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'index'])
                ->middleware('permission:view master data schedules')
                ->name('api.schedules.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'store'])
                ->middleware('permission:create master data schedules')
                ->name('api.schedules.store');

            Route::prefix('{schedule}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'show'])
                    ->middleware('permission:view master data schedules')
                    ->name('api.schedules.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'update'])
                    ->middleware('permission:update master data schedules')
                    ->name('api.schedules.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\ScheduleController::class, 'destroy'])
                    ->middleware('permission:delete master data schedules')
                    ->name('api.schedules.destroy');
            });
        });

        Route::prefix('overtimes')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'index'])
                ->middleware('permission:view master data overtimes')
                ->name('api.overtimes.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'store'])
                ->middleware('permission:create master data overtimes')
                ->name('api.overtimes.store');

            Route::prefix('{overtime}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'show'])
                    ->middleware('permission:view master data overtimes')
                    ->name('api.overtimes.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'update'])
                    ->middleware('permission:update master data overtimes')
                    ->name('api.overtimes.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\OvertimeController::class, 'destroy'])
                    ->middleware('permission:delete master data overtimes')
                    ->name('api.overtimes.destroy');
            });
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\UserController::class, 'index'])
                ->middleware('permission:view master data employees')
                ->name('api.users.index');

            Route::post('/', [\App\Http\Controllers\Api\UserController::class, 'store'])
                ->middleware('permission:create master data employees')
                ->name('api.users.store');

            Route::prefix('{user}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\UserController::class, 'show'])
                    ->middleware('permission:view master data employees')
                    ->name('api.users.show');

                Route::put('/', [\App\Http\Controllers\Api\UserController::class, 'update'])
                    ->middleware('permission:edit master data employees')
                    ->name('api.users.update');

                Route::delete('/', [\App\Http\Controllers\Api\UserController::class, 'destroy'])
                    ->middleware('permission:delete master data employees')
                    ->name('api.users.destroy');
            });
        });
        Route::prefix('roles')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\RoleController::class, 'index'])
                ->middleware('permission:view master data roles')
                ->name('api.roles.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\RoleController::class, 'store'])
                ->middleware('permission:create master data roles')
                ->name('api.roles.store');

            Route::prefix('{role}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\RoleController::class, 'show'])->name('api.roles.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\RoleController::class, 'update'])
                    ->middleware('permission:edit master data roles')
                    ->name('api.roles.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\RoleController::class, 'destroy'])
                    ->middleware('permission:delete master data roles')
                    ->name('api.roles.destroy');
            });

            Route::get('/stats', [\App\Http\Controllers\Api\MasterData\RoleController::class, 'stats'])->name('api.roles.stats');
            Route::post('/bulk-assign-permissions', [\App\Http\Controllers\Api\MasterData\RoleController::class, 'bulkAssignPermissions'])
                ->middleware('permission:sync master data roles permissions')
                ->name('api.roles.bulk-assign-permissions');
            Route::post('/bulk-revoke-permissions', [\App\Http\Controllers\Api\MasterData\RoleController::class, 'bulkRevokePermissions'])
                ->middleware('permission:sync master data roles permissions')
                ->name('api.roles.bulk-revoke-permissions');
        });

        Route::prefix('permissions')->middleware(['auth:sanctum', 'permission:view master data permissions'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'index'])->name('api.permissions.index');
            Route::post('/', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'store'])
                ->middleware('permission:create master data permissions')
                ->name('api.permissions.store');

            Route::prefix('{permission}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'show'])->name('api.permissions.show');
                Route::put('/', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'update'])
                    ->middleware('permission:update master data permissions')
                    ->name('api.permissions.update');
                Route::delete('/', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'destroy'])
                    ->middleware('permission:delete master data permissions')
                    ->name('api.permissions.destroy');
            });
            Route::get('/stats', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'stats'])->name('api.permissions.stats');
            Route::get('/search', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'search'])->name('api.permissions.search');
            Route::get('/guard/{guardName}', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'getByGuard'])->name('api.permissions.by-guard');
            Route::get('/exists/{name}', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'checkExists'])->name('api.permissions.exists');

            Route::get('/categories', [\App\Http\Controllers\Api\MasterData\PermissionController::class, 'getCategories'])->name('api.permissions.categories');
        });

        Route::prefix('roles')->group(function () {
            Route::get('/{role}/permissions', [\App\Http\Controllers\Api\MasterData\RolePermissionController::class, 'index'])
                ->middleware('permission:view master data roles')
                ->name('api.roles.permissions.index');

            Route::put('/{role}/permissions', [\App\Http\Controllers\Api\MasterData\RolePermissionController::class, 'sync'])
                ->middleware('permission:update master data roles')
                ->name('api.roles.permissions.sync');

            Route::delete('/{role}/permissions/{permission}', [\App\Http\Controllers\Api\MasterData\RolePermissionController::class, 'revoke'])
                ->middleware('permission:update master data roles')
                ->name('api.roles.permissions.revoke');

            Route::post('/{role}/permissions/{permission}', [\App\Http\Controllers\Api\MasterData\RolePermissionController::class, 'give'])
                ->middleware('permission:sync master data roles permissions')
                ->name('api.roles.permissions.give');

            Route::get('/{role}/has-permission/{permission}', [\App\Http\Controllers\Api\MasterData\RolePermissionController::class, 'checkPermission'])
                ->middleware('permission:view master data roles')
                ->name('api.roles.has-permission');

            Route::get('/{role}/users', [\App\Http\Controllers\Api\MasterData\RolePermissionController::class, 'users'])
                ->middleware('permission:view master data roles')
                ->name('api.roles.users');

            Route::get('/{role}/available-permissions', [\App\Http\Controllers\Api\MasterData\RolePermissionController::class, 'available'])
                ->middleware('permission:view master data roles')
                ->name('api.roles.available-permissions');
        });
    });

    Route::prefix('transaction')->group(function () {

        Route::prefix('attendances')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Transaction\AttendanceController::class, 'index'])
                ->middleware('permission:view transactions attendances')
                ->name('api.attendances.index');
            Route::post('/', [\App\Http\Controllers\Api\Transaction\AttendanceController::class, 'store'])
                ->middleware('permission:create transactions attendances')
                ->name('api.attendances.store');

            Route::prefix('{attendance}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Transaction\AttendanceController::class, 'show'])
                    ->middleware('permission:view transactions attendances')
                    ->name('api.attendances.show');
                Route::put('/', [\App\Http\Controllers\Api\Transaction\AttendanceController::class, 'update'])
                    ->middleware('permission:update transactions attendances')
                    ->name('api.attendances.update');
                Route::delete('/', [\App\Http\Controllers\Api\Transaction\AttendanceController::class, 'destroy'])
                    ->middleware('permission:delete transactions attendances')
                    ->name('api.attendances.destroy');
            });
        });
        Route::prefix('leaves')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'index'])
                ->middleware('permission:view transactions leaves')
                ->name('api.leaves.index');
            Route::post('/', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'store'])
                ->middleware('permission:create transactions leaves')
                ->name('api.leaves.store');

            Route::prefix('{leave}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'show'])
                    ->middleware('permission:view transactions leaves')
                    ->name('api.leaves.show');
                Route::put('/', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'update'])
                    ->middleware('permission:update transactions leaves')
                    ->name('api.leaves.update');
                Route::delete('/', [\App\Http\Controllers\Api\Transaction\LeaveController::class, 'destroy'])
                    ->middleware('permission:delete transactions leaves')
                    ->name('api.leaves.destroy');
            });
        });

        Route::prefix('debts')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'index'])
                ->middleware('permission:view transactions debts')
                ->name('api.debts.index');
            Route::post('/', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'store'])
                ->middleware('permission:create transactions debts')
                ->name('api.debts.store');

            Route::prefix('{debt}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'show'])
                    ->middleware('permission:view transactions debts')
                    ->name('api.debts.show');
                Route::put('/', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'update'])
                    ->middleware('permission:update transactions debts')
                    ->name('api.debts.update');
                Route::delete('/', [\App\Http\Controllers\Api\Transaction\DebtController::class, 'destroy'])
                    ->middleware('permission:delete transactions debts')
                    ->name('api.debts.destroy');
            });
        });

        Route::prefix('reimbursements')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'index'])
                ->middleware('permission:view transactions reimbursements')
                ->name('api.reimbursements.index');
            Route::post('/', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'store'])
                ->middleware('permission:create transactions reimbursements')
                ->name('api.reimbursements.store');

            Route::prefix('{reimburse}')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'show'])
                    ->middleware('permission:view transactions reimbursements')
                    ->name('api.reimbursements.show');
                Route::put('/', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'update'])
                    ->middleware('permission:update transactions reimbursements')
                    ->name('api.reimbursements.update');
                Route::delete('/', [\App\Http\Controllers\Api\Transaction\ReimburseController::class, 'destroy'])
                    ->middleware('permission:delete transactions reimbursements')
                    ->name('api.reimbursements.destroy');
            });
        });
    });

    Route::prefix('report')->group(function () {
        Route::prefix('leaves')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Reports\LeaveController::class, 'index'])
                ->middleware('permission:view reports leaves')
                ->name('api.leaves.index');
        });

        Route::prefix('attendances')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Reports\AttendanceController::class, 'index'])
                ->middleware('permission:view reports attendances')
                ->name('api.attendances.index');
            Route::get('/{employee_id}/{period}', [\App\Http\Controllers\Api\Reports\AttendanceController::class, 'detail'])
                ->middleware('permission:view reports attendances')
                ->name('api.attendances.detail');
        });
        Route::prefix('overtimes')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Reports\OvertimeController::class, 'index'])
                ->middleware('permission:view reports overtimes')
                ->name('api.overtimes.index');
        });
        Route::prefix('debts')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Reports\DebtController::class, 'index'])
                ->middleware('permission:view reports debts')
                ->name('api.debts.index');
        });

        Route::prefix('reimbursements')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Reports\ReimbursementController::class, 'index'])
                ->middleware('permission:view reports reimbursements')
                ->name('api.reimbursements.index');
        });
    });
});
require __DIR__ . '/templates.php';
