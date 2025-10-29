<?php

use App\Http\Controllers\ApplicationOpportunityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Opportunity Routes
|--------------------------------------------------------------------------
|
| Admin routes: Only authenticated admins can perform application operations
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Admin routes
    Route::prefix('admin')->group(function () {
        // Get students for invitation
        Route::get('students/for-invitation', [ApplicationOpportunityController::class, 'getStudentsForInvitation']);
        // View opportunity applications
        Route::get('opportunities/{opportunityId}/applications', [ApplicationOpportunityController::class, 'getOpportunityApplications']);
        // Invite students to opportunity (single or multiple)
        Route::post('opportunities/{opportunityId}/invite', [ApplicationOpportunityController::class, 'inviteMultipleStudents']);

        // Excuse management
        Route::get('applications/{applicationId}/excuse', [ApplicationOpportunityController::class, 'getExcuseDetails']);
        Route::patch('applications/{applicationId}/approve-excuse', [ApplicationOpportunityController::class, 'approveExcuse']);
        Route::patch('applications/{applicationId}/reject-excuse', [ApplicationOpportunityController::class, 'rejectExcuse']);

        // Application management
        Route::delete('opportunities/applications/{applicationId}', [ApplicationOpportunityController::class, 'deleteApplication']);

        // Get opportunity attendance (accepted/attend applications)
        Route::get('opportunities/{opportunityId}/attendance', [ApplicationOpportunityController::class, 'getOpportunityAttendance']);

        // Update application statuses
        Route::patch('opportunities/{opportunityId}/applications/status', [ApplicationOpportunityController::class, 'updateApplicationStatus']);

        // Generate certificate tokens
        Route::post('opportunities/{opportunityId}/generate-certificates', [ApplicationOpportunityController::class, 'generateMissingCertificateTokens']);
    });

    // Student routes
    Route::prefix('student')->group(function () {
        // Application management
        Route::patch('applications/{applicationId}/accept', [ApplicationOpportunityController::class, 'acceptInvitation']);
        Route::post('applications/{applicationId}/reject', [ApplicationOpportunityController::class, 'rejectInvitation']);

        // QR attendance
        Route::patch('applications/{applicationId}/attendance', [ApplicationOpportunityController::class, 'qrAttendance']);

        // View my applications
        Route::get('applications', [ApplicationOpportunityController::class, 'getMyApplications']);
        // View my opportunities
        Route::get('opportunities', [ApplicationOpportunityController::class, 'getOpportunitiesForStudent']);
        // Get my opportunity application by Opportunity ID (student only)
        Route::get('opportunities/{opportunityId}/my-application', [ApplicationOpportunityController::class, 'getMyOpportunityApplication']);
    });

    // Get opportunity by ID (accessible to all authenticated users)
    Route::get('opportunities/{opportunityId}', [ApplicationOpportunityController::class, 'getOpportunityById']);

    // QR attendance routes
    Route::post('opportunities/qr/{token}/attendance', [ApplicationOpportunityController::class, 'qrAttendanceWithToken']);
    Route::post('opportunities/qr/{token}/mark-attendance', [ApplicationOpportunityController::class, 'markAttendanceViaQR']);
});

// Public routes (no authentication required)
// Certificate access
Route::get('certificates/{token}', [ApplicationOpportunityController::class, 'getCertificate']);
