<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;

/*
|--------------------------------------------------------------------------
| Program Routes
|--------------------------------------------------------------------------
|
| Admin routes: Only authenticated admins can perform program operations
|
*/

// Admin-only routes - require authentication and admin role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Program CRUD operations
    Route::get('admin/programs', [ProgramController::class, 'index']);
    Route::post('admin/programs', [ProgramController::class, 'store']);
    Route::get('admin/programs/{id}', [ProgramController::class, 'show']);
    Route::match(['put', 'patch'], 'admin/programs/{id}', [ProgramController::class, 'update']);
    Route::delete('admin/programs/{id}', [ProgramController::class, 'destroy']);

    // Program status management
    Route::patch('admin/programs/{id}/status', [ProgramController::class, 'changeStatus']);

    // Program statistics
    Route::get('admin/programs/statistics', [ProgramController::class, 'getStatistics']);
});

// Public QR Code routes (no authentication required)
Route::get('programs/qr/{token}', [ProgramController::class, 'qrScan']);
