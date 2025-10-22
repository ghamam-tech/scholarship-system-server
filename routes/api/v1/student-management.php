<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentStatusTrailController;
use App\Http\Controllers\SemesterController;

/*
|--------------------------------------------------------------------------
| Student Management Routes (Admin Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    // Student Status Trail Management
    Route::get('admin/students/{studentId}/status-trail', [StudentStatusTrailController::class, 'getStatusTrail']);
    Route::post('admin/students/{studentId}/status', [StudentStatusTrailController::class, 'addStatus']);
    Route::get('admin/students/by-status/{status}', [StudentStatusTrailController::class, 'getStudentsByStatus']);
    Route::get('admin/students/with-warnings', [StudentStatusTrailController::class, 'getStudentsWithWarnings']);
    Route::get('admin/students/requesting-meetings', [StudentStatusTrailController::class, 'getStudentsRequestingMeetings']);

    // Note: Semester management routes are in student-semester.php
});
