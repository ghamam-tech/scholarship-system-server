<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
|
| Admin routes: Only authenticated admins can perform student operations
|
*/

// Admin-only routes - require authentication and admin role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Create new student (email and password only)
    Route::post('admin/students', [StudentController::class, 'createStudent']);

    // Get all students
    Route::get('admin/students', [StudentController::class, 'getAllStudents']);

    // Get specific student by ID
    Route::get('admin/students/{id}', [StudentController::class, 'getStudentById']);
});
