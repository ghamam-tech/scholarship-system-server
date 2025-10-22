<?php
use App\Http\Controllers\ApprovedApplicantApplicationController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/admin/applications/approve-application', [ApprovedApplicantApplicationController::class, 'store']);
    });
});
