<?php

use App\Http\Controllers\ApplicantApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\QualificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // Applicant Profile
    Route::post('/applicant/complete-profile', [ApplicantController::class, 'completeProfile']);
    
    // Qualifications
    Route::get('/qualifications', [QualificationController::class, 'index']);
    Route::post('/qualifications', [QualificationController::class, 'store']);
    
    // Applications
    Route::post('/applications/submit-complete', [ApplicantApplicationController::class, 'submitCompleteApplication']);
    Route::post('/applications', [ApplicantApplicationController::class, 'store']);
    Route::get('/applications', [ApplicantApplicationController::class, 'index']);
    Route::get('/applications/{id}', [ApplicantApplicationController::class, 'show']);
    Route::put('/applications/{id}/program-details', [ApplicantApplicationController::class, 'updateProgramDetails']);
    
    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/applications', [ApplicantApplicationController::class, 'getAllApplications']);
        Route::put('/applications/{id}/status', [ApplicantApplicationController::class, 'updateStatus']);
        Route::get('/admin/statistics', [ApplicantApplicationController::class, 'getStatistics']);
    });
});