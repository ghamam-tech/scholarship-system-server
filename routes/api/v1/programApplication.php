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
    // Invite students to program (single or multiple)
    Route::post('admin/programs/{programId}/invite', [ProgramApplicationController::class, 'inviteMultipleStudents']);

    // Manage student excuses
    Route::patch('admin/applications/{applicationId}/approve-excuse', [ProgramApplicationController::class, 'approveExcuse']);
    Route::patch('admin/applications/{applicationId}/reject-excuse', [ProgramApplicationController::class, 'rejectExcuse']);

    // View program applications
    Route::get('admin/programs/{programId}/applications', [ProgramApplicationController::class, 'getProgramApplications']);
});

// Student-only routes - require authentication and student role
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    // Respond to invitations
    Route::patch('student/applications/{applicationId}/accept', [ProgramApplicationController::class, 'acceptInvitation']);
    Route::patch('student/applications/{applicationId}/reject', [ProgramApplicationController::class, 'rejectInvitation']);

    // QR Code attendance
    Route::patch('student/applications/{applicationId}/attendance', [ProgramApplicationController::class, 'qrAttendance']);

    // View my applications
    Route::get('student/applications', [ProgramApplicationController::class, 'getMyApplications']);
});

// Student QR Code attendance route (requires student authentication)
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::post('programs/qr/{token}/attendance', [ProgramApplicationController::class, 'qrAttendanceWithToken']);
});
