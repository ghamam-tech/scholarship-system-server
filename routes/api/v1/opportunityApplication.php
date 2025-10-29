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
        Route::get('admin/opportunities/students/for-invitation', [ApplicationOpportunityController::class, 'getStudentsForInvitation']);
        // View opportunity applications
        Route::get('admin/opportunities/{opportunityId}/applications', [ApplicationOpportunityController::class, 'getOpportunityApplications']);
        // Invite students to opportunity (single or multiple)
        Route::post('admin/opportunities/{opportunityId}/invite', [ApplicationOpportunityController::class, 'inviteMultipleStudents']);

        // Excuse management
        Route::get('admin/opportunities/applications/{applicationId}/excuse', [ApplicationOpportunityController::class, 'getExcuseDetails']);
        Route::patch('admin/opportunities/applications/{applicationId}/approve-excuse', [ApplicationOpportunityController::class, 'approveExcuse']);
        Route::patch('admin/opportunities/applications/{applicationId}/reject-excuse', [ApplicationOpportunityController::class, 'rejectExcuse']);

        // Application management
        Route::delete('admin/opportunities/applications/{applicationId}', [ApplicationOpportunityController::class, 'deleteApplication']);

        // Get opportunity attendance (accepted/attend applications)
        Route::get('admin/opportunities/{opportunityId}/attendance', [ApplicationOpportunityController::class, 'getOpportunityAttendance']);

        // Update application statuses
        Route::patch('admin/opportunities/{opportunityId}/applications/status', [ApplicationOpportunityController::class, 'updateApplicationStatus']);

        // Generate certificate tokens
        Route::post('admin/opportunities/{opportunityId}/generate-certificates', [ApplicationOpportunityController::class, 'generateMissingCertificateTokens']);
    });

    // Student routes
    Route::prefix('student')->group(function () {
        // Application management
        Route::patch('student/opportunities/applications/{applicationId}/accept', [ApplicationOpportunityController::class, 'acceptInvitation']);
        Route::post('student/opportunities/applications/{applicationId}/reject', [ApplicationOpportunityController::class, 'rejectInvitation']);

        // QR attendance
        Route::patch('student/opportunities/applications/{applicationId}/attendance', [ApplicationOpportunityController::class, 'qrAttendance']);

        // View my applications
        Route::get('student/opportunities/applications', [ApplicationOpportunityController::class, 'getMyApplications']);
        // View my opportunities
        Route::get('student/opportunities', [ApplicationOpportunityController::class, 'getOpportunitiesForStudent']);
        // Get my opportunity application by Opportunity ID (student only)
        Route::get('student/opportunities/{opportunityId}/my-application', [ApplicationOpportunityController::class, 'getMyOpportunityApplication']);
    });

    // Get opportunity by ID (accessible to all authenticated users)
    Route::get('admin/opportunities/{opportunityId}', [ApplicationOpportunityController::class, 'getOpportunityById']);

    // QR attendance routes
    Route::post('admin/opportunities/qr/{token}/attendance', [ApplicationOpportunityController::class, 'qrAttendanceWithToken']);
    Route::post('admin/opportunities/qr/{token}/mark-attendance', [ApplicationOpportunityController::class, 'markAttendanceViaQR']);
});

// Public routes (no authentication required)
// Certificate access
Route::get('certificates/{token}', [ApplicationOpportunityController::class, 'getCertificate']);
