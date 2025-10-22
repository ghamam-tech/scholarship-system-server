<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterController;

/*
|--------------------------------------------------------------------------
| Student Semester Management Routes
|--------------------------------------------------------------------------
*/

// Student routes - students can manage their own semesters
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    // Get student's own semesters
    Route::get('student/semesters', [SemesterController::class, 'getMySemesters']);

    // Create new semester for student
    Route::post('student/semesters', [SemesterController::class, 'createMySemester']);

    // Update student's own semester
    Route::put('student/semesters/{semesterId}', [SemesterController::class, 'updateMySemester']);

    // Get student's semester statistics
    Route::get('student/semester-statistics', [SemesterController::class, 'getMySemesterStatistics']);
});

// Admin routes - admins can manage any student's semesters
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Get specific student's semesters (admin view)
    Route::get('admin/students/{studentId}/semesters', [SemesterController::class, 'getStudentSemesters']);

    // Create semester for any student (admin)
    Route::post('admin/students/{studentId}/semesters', [SemesterController::class, 'createSemester']);

    // Update any semester (admin)
    Route::put('admin/semesters/{semesterId}', [SemesterController::class, 'updateSemester']);

    // Get semester statistics for any student (admin)
    Route::get('admin/students/{studentId}/semester-statistics', [SemesterController::class, 'getSemesterStatistics']);

    // Get all active semesters (admin)
    Route::get('admin/semesters/active', [SemesterController::class, 'getAllActiveSemesters']);
});
