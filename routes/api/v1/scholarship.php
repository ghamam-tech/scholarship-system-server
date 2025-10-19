<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScholarshipController;

/*
|--------------------------------------------------------------------------
| Scholarship Routes
|--------------------------------------------------------------------------
|
| Public routes: Anyone can view active, non-hidden scholarships
| Admin routes: Only authenticated admins can perform CRUD operations
|
*/

// Public routes - anyone can view active, non-hidden scholarships
// These routes work for both authenticated and unauthenticated users
Route::get('scholarships', [ScholarshipController::class, 'index']);
Route::get('scholarships/{scholarship}', [ScholarshipController::class, 'show']);

// Helper route for frontend - get universities by countries
Route::get('scholarships/universities/by-countries', [ScholarshipController::class, 'getUniversitiesByCountries']);

// Admin-only routes - require authentication and admin role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin can view ALL scholarships (including expired/hidden ones)
    Route::get('admin/scholarships/all', [ScholarshipController::class, 'adminIndex']);
    Route::get('admin/scholarships/{scholarship}', [ScholarshipController::class, 'adminShow']);

    // CRUD operations for scholarships
    Route::post('admin/scholarships', [ScholarshipController::class, 'store']);
    Route::match(['put', 'patch'], 'admin/scholarships/{scholarship}', [ScholarshipController::class, 'update']);
    Route::delete('admin/scholarships/{scholarship}', [ScholarshipController::class, 'destroy']);

    // Debug route for testing authentication
    Route::get('scholarships/debug/user', [ScholarshipController::class, 'debugUser']);
});
