<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:student'])->group(function () {
        Route::get('/student/status-summary', [StudentController::class, 'statusSummary']);
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/students', [StudentController::class, 'index']);
        Route::get('/admin/students/{student}', [StudentController::class, 'show']);
        Route::post('/admin/students/add-manually', [StudentController::class, 'addStudentManually']);
        Route::post('/admin/students/{studentId}/warning/first', [StudentController::class, 'issueFirstWarning']);
        Route::post('/admin/students/{studentId}/warning/second', [StudentController::class, 'issueSecondWarning']);
        Route::post('/admin/students/{studentId}/terminate', [StudentController::class, 'terminateStudent']);
        Route::post('/admin/students/{studentId}/graduate', [StudentController::class, 'graduateStudent']);
        Route::post('/admin/students/{studentId}/request-meeting', [StudentController::class, 'requestMeeting']);
    });
});
