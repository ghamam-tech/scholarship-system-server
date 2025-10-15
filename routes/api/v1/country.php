<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryController;

/*
|--------------------------------------------------------------------------
| Country Routes
|--------------------------------------------------------------------------
*/

// Public routes - anyone can view active countries
Route::get('countries', [CountryController::class, 'index']);
Route::get('countries/{country}', [CountryController::class, 'show']);

// Admin-only routes - protected by middleware
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('countries', [CountryController::class, 'store']);
    Route::match(['put', 'patch'], 'countries/{country}', [CountryController::class, 'update']);
    Route::delete('countries/{country}', [CountryController::class, 'destroy']);
});