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
    Route::get('admin/countries', [CountryController::class, 'index']);
    Route::get('admin/countries/{country}', [CountryController::class, 'show']);
    Route::post('admin/countries', [CountryController::class, 'store']);
    Route::match(['put', 'patch'], 'admin/countries/{country}', [CountryController::class, 'update']);
    Route::delete('admin/countries/{country}', [CountryController::class, 'destroy']);
});
