<?php

use App\Http\Controllers\ApplicantApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\QualificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // Applicant Profile Management
    Route::post('/applicant/complete-profile', [ApplicantController::class, 'completeProfile']);
    Route::put('/applicant/profile', [ApplicantController::class, 'updateProfile']);
    Route::get('/applicant/profile', [ApplicantController::class, 'getProfile']);

    // Qualifications Management
    Route::post('/applicant/qualifications', [ApplicantController::class, 'addQualification']);
    Route::put('/applicant/qualifications/{qualificationId}', [ApplicantController::class, 'updateQualification']);
    Route::delete('/applicant/qualifications/{qualificationId}', [ApplicantController::class, 'deleteQualification']);

    // Application Submission (after profile completion)
    Route::post('/applications', [ApplicantApplicationController::class, 'store']);
    Route::post('/applications/submit-complete', [ApplicantApplicationController::class, 'submitCompleteApplication']);
    Route::get('/applications', [ApplicantApplicationController::class, 'index']);
    Route::get('/applications/{id}', [ApplicantApplicationController::class, 'show']);
    Route::put('/applications/{id}/program-details', [ApplicantApplicationController::class, 'updateProgramDetails']);

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/applications', [ApplicantApplicationController::class, 'getAllApplications']);
        Route::post('/applications/{id}/status', [ApplicantApplicationController::class, 'addStatus']);        Route::get('/admin/statistics', [ApplicantApplicationController::class, 'getStatistics']);
        Route::delete('/admin/applications/{id}', [ApplicantApplicationController::class, 'destroy']);
    });
});
