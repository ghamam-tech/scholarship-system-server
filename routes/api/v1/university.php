<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UniversityController;

/*
|--------------------------------------------------------------------------
| University Routes
|--------------------------------------------------------------------------
*/

// Public routes - anyone can view active universities only
Route::get('universities', [UniversityController::class, 'index']);
Route::get('universities/{university}', [UniversityController::class, 'show']);

// Admin-only routes - protected by middleware
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin can view all universities (active + inactive)
    Route::get('admin/universities', [UniversityController::class, 'index']);
    Route::get('admin/universities/{university}', [UniversityController::class, 'show']);

    // Admin CRUD operations
    Route::post('admin/universities', [UniversityController::class, 'store']);
    Route::match(['put', 'patch'], 'admin/universities/{university}', [UniversityController::class, 'update']);
    Route::delete('admin/universities/{university}', [UniversityController::class, 'destroy']);
});
