<?php

use App\Http\Controllers\OpportunityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Opportunity Routes
|--------------------------------------------------------------------------
|
| Admin routes: Only authenticated admins can perform opportunity operations
|
*/

// Admin-only routes - require authentication and admin role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Opportunity CRUD operations
    Route::get('admin/opportunities', [OpportunityController::class, 'index']);
    Route::post('admin/opportunities', [OpportunityController::class, 'store']);
    Route::get('admin/opportunities/{id}', [OpportunityController::class, 'show']);
    Route::match(['put', 'patch'], 'admin/opportunities/{id}', [OpportunityController::class, 'update']);
    Route::delete('admin/opportunities/{id}', [OpportunityController::class, 'destroy']);

    // Opportunity status management
    Route::patch('admin/opportunities/{id}/status', [OpportunityController::class, 'changeStatus']);

    // Opportunity statistics
    Route::get('admin/opportunities/statistics', [OpportunityController::class, 'getStatistics']);
});

// Public routes (no authentication required)
Route::get('opportunities/qr/{token}', [OpportunityController::class, 'qrScan']);
