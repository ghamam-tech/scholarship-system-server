<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScholarshipController;

// Public routes - anyone can view active scholarships
Route::get('scholarships', [ScholarshipController::class, 'index']);
Route::get('scholarships/{scholarship}', [ScholarshipController::class, 'show']);

// Get universities by countries (for frontend)
Route::get('scholarships/universities/by-countries', [ScholarshipController::class, 'getUniversitiesByCountries']);

// Admin-only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('scholarships', [ScholarshipController::class, 'store']);
    Route::match(['put', 'patch'], 'scholarships/{scholarship}', [ScholarshipController::class, 'update']);
    Route::delete('scholarships/{scholarship}', [ScholarshipController::class, 'destroy']);
});