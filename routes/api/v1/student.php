<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

/*
|--------------------------------------------------------------------------
| Student Management Routes (Admin Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Final approval with scholarship assignment → promote applicant to student
    Route::post('admin/applications/{applicationId}/final-approval-with-scholarship', [StudentController::class, 'finalApprovalWithScholarship']);

    // Legacy: Final approval → promote applicant to student
    Route::post('admin/applications/{applicationId}/assign-final-scholarship', [StudentController::class, 'assignFinalScholarship']);

    // Graduation → demote student to applicant
    Route::post('admin/students/{studentId}/graduate', [StudentController::class, 'graduate']);

    // Suspend student scholarship → demote student to applicant
    Route::post('admin/students/{studentId}/suspend', [StudentController::class, 'suspend']);

    // Get all students
    Route::get('admin/students', [StudentController::class, 'index']);

    // Get specific student
    Route::get('admin/students/{studentId}', [StudentController::class, 'show']);
});
