<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramApplicationController;

/*
|--------------------------------------------------------------------------
| Program Application Routes
|--------------------------------------------------------------------------
|
| Admin routes: Invite students, manage applications
| Student routes: Accept/reject invitations, mark attendance
|
*/

// Admin-only routes - require authentication and admin role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Get students for invitation
    Route::get('admin/programs/students/for-invitation', [ProgramApplicationController::class, 'getStudentsForInvitation']);
    // View program applications
    Route::get('admin/programs/{programId}/applications', [ProgramApplicationController::class, 'getProgramApplications']);
    // Invite students to program (single or multiple)
    Route::post('admin/programs/{programId}/invite', [ProgramApplicationController::class, 'inviteMultipleStudents']);

    // Manage student excuses
    Route::get('admin/programs/applications/{applicationId}/excuse', [ProgramApplicationController::class, 'getExcuseDetails']);
    Route::patch('admin/programs/applications/{applicationId}/approve-excuse', [ProgramApplicationController::class, 'approveExcuse']);
    Route::patch('admin/programs/applications/{applicationId}/reject-excuse', [ProgramApplicationController::class, 'rejectExcuse']);

    // Delete application
    Route::delete('admin/programs/applications/{applicationId}', [ProgramApplicationController::class, 'deleteApplication']);

    // Get program attendance (accepted/attend applications)
    Route::get('admin/programs/{programId}/attendance', [ProgramApplicationController::class, 'getProgramAttendance']);

    // Update application status (accepted/attend) for multiple applications
    Route::patch('admin/programs/{programId}/applications/status', [ProgramApplicationController::class, 'updateApplicationStatus']);

    // Generate missing certificate tokens for existing attendance records
    Route::post('admin/programs/{programId}/generate-certificates', [ProgramApplicationController::class, 'generateMissingCertificateTokens']);
});

// Student-only routes - require authentication and student role
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    // Respond to invitations
    Route::patch('student/programs/applications/{applicationId}/accept', [ProgramApplicationController::class, 'acceptInvitation']);
    Route::post('student/programs/applications/{applicationId}/reject', [ProgramApplicationController::class, 'rejectInvitation']);

    // QR Code attendance
    Route::patch('student/programs/applications/{applicationId}/attendance', [ProgramApplicationController::class, 'qrAttendance']);

    // View my applications
    Route::get('student/programs/applications', [ProgramApplicationController::class, 'getMyApplications']);
    // View my programs
    Route::get('student/programs', [ProgramApplicationController::class, 'getProgramsForStudent']);
    // Get my program application by Program ID (student only)
    Route::get('student/programs/{programId}/my-application', [ProgramApplicationController::class, 'getMyProgramApplication']);
});

// Get program by ID (accessible to all authenticated users)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('programs/{programId}', [ProgramApplicationController::class, 'getProgramById']);
});

// Student QR Code attendance routes (requires student authentication)
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::post('programs/qr/{token}/attendance', [ProgramApplicationController::class, 'qrAttendanceWithToken']);
    Route::post('programs/qr/{token}/mark-attendance', [ProgramApplicationController::class, 'markAttendanceViaQR']);
});

// Public certificate route (no authentication required)
// Certificate access for programs
Route::get('program-certificates/{token}', [ProgramApplicationController::class, 'getCertificate']);
