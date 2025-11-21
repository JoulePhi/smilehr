<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RolePermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
});


Route::prefix('master-data')->group(function () {
    Route::prefix('banners')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\BannerController::class, 'index'])
            ->middleware('permission:view master data home')
            ->name('api.banners.index');
        Route::post('/', [\App\Http\Controllers\Api\BannerController::class, 'store'])
            ->middleware('permission:create master data home')
            ->name('api.banners.store');

        Route::prefix('{banner}')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\BannerController::class, 'show'])->name('api.banners.show');
            Route::put('/', [\App\Http\Controllers\Api\BannerController::class, 'update'])
                ->middleware('permission:edit master data home')
                ->name('api.banners.update');
            Route::delete('/', [\App\Http\Controllers\Api\BannerController::class, 'destroy'])
                ->middleware('permission:delete master data home')
                ->name('api.banners.destroy');
        });
    });
});

Route::prefix('roles')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [RoleController::class, 'index'])
        ->middleware('permission:view master data roles')
        ->name('api.roles.index');
    Route::post('/', [RoleController::class, 'store'])
        ->middleware('permission:create master data roles')
        ->name('api.roles.store');

    Route::prefix('{role}')->group(function () {
        Route::get('/', [RoleController::class, 'show'])->name('api.roles.show');
        Route::put('/', [RoleController::class, 'update'])
            ->middleware('permission:edit master data roles')
            ->name('api.roles.update');
        Route::delete('/', [RoleController::class, 'destroy'])
            ->middleware('permission:delete master data roles')
            ->name('api.roles.destroy');
    });

    Route::get('/stats', [RoleController::class, 'stats'])->name('api.roles.stats');
    Route::post('/bulk-assign-permissions', [RoleController::class, 'bulkAssignPermissions'])
        ->middleware('permission:sync master data roles permissions')
        ->name('api.roles.bulk-assign-permissions');
    Route::post('/bulk-revoke-permissions', [RoleController::class, 'bulkRevokePermissions'])
        ->middleware('permission:sync master data roles permissions')
        ->name('api.roles.bulk-revoke-permissions');
});

Route::prefix('permissions')->middleware(['auth:sanctum', 'permission:view master data permissions'])->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('api.permissions.index');
    Route::post('/', [PermissionController::class, 'store'])
        ->middleware('permission:create master data permissions')
        ->name('api.permissions.store');

    Route::prefix('{permission}')->group(function () {
        Route::get('/', [PermissionController::class, 'show'])->name('api.permissions.show');
        Route::put('/', [PermissionController::class, 'update'])
            ->middleware('permission:edit master data permissions')
            ->name('api.permissions.update');
        Route::delete('/', [PermissionController::class, 'destroy'])
            ->middleware('permission:delete master data permissions')
            ->name('api.permissions.destroy');
    });
    Route::get('/stats', [PermissionController::class, 'stats'])->name('api.permissions.stats');
    Route::get('/search', [PermissionController::class, 'search'])->name('api.permissions.search');
    Route::get('/guard/{guardName}', [PermissionController::class, 'getByGuard'])->name('api.permissions.by-guard');
    Route::get('/exists/{name}', [PermissionController::class, 'checkExists'])->name('api.permissions.exists');

    Route::get('/categories', [PermissionController::class, 'getCategories'])->name('api.permissions.categories');
});

Route::prefix('roles')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/{role}/permissions', [RolePermissionController::class, 'index'])
        ->middleware('permission:view master data roles')
        ->name('api.roles.permissions.index');

    Route::put('/{role}/permissions', [RolePermissionController::class, 'sync'])
        ->middleware('permission:update master data roles')
        ->name('api.roles.permissions.sync');

    Route::delete('/{role}/permissions/{permission}', [RolePermissionController::class, 'revoke'])
        ->middleware('permission:update master data roles')
        ->name('api.roles.permissions.revoke');

    Route::post('/{role}/permissions/{permission}', [RolePermissionController::class, 'give'])
        ->middleware('permission:sync master data roles permissions')
        ->name('api.roles.permissions.give');

    Route::get('/{role}/has-permission/{permission}', [RolePermissionController::class, 'checkPermission'])
        ->middleware('permission:view master data roles')
        ->name('api.roles.has-permission');

    Route::get('/{role}/users', [RolePermissionController::class, 'users'])
        ->middleware('permission:view master data roles')
        ->name('api.roles.users');

    Route::get('/{role}/available-permissions', [RolePermissionController::class, 'available'])
        ->middleware('permission:view master data roles')
        ->name('api.roles.available-permissions');
});
