<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/admin/students/{studentId}/warning/first', [StudentController::class, 'issueFirstWarning']);
        Route::post('/admin/students/{studentId}/warning/second', [StudentController::class, 'issueSecondWarning']);
        Route::post('/admin/students/{studentId}/terminate', [StudentController::class, 'terminateStudent']);
        Route::post('/admin/students/{studentId}/graduate', [StudentController::class, 'graduateStudent']);
    });
});
