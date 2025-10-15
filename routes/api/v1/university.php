<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UniversityController;

/*
|--------------------------------------------------------------------------
| University Routes
|--------------------------------------------------------------------------
*/

// Public routes - anyone can view active universities
Route::get('universities', [UniversityController::class, 'index']);
Route::get('universities/{university}', [UniversityController::class, 'show']);

// Admin-only routes - protected by middleware
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('universities', [UniversityController::class, 'store']);
    Route::match(['put', 'patch'], 'universities/{university}', [UniversityController::class, 'update']);
    Route::delete('universities/{university}', [UniversityController::class, 'destroy']);
});