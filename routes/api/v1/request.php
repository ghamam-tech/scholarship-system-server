<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/student/requests', [RequestController::class, 'index']);
    Route::post('/student/requests', [RequestController::class, 'store']);
    Route::get('/student/requests/{requestId}', [RequestController::class, 'show'])
        ->whereNumber('requestId');
    Route::post('/student/requests/{requestId}/submit-document', [RequestController::class, 'submitRequestedDocument'])
        ->whereNumber('requestId');
    Route::post('/student/requests/{requestId}/schedule-meeting', [RequestController::class, 'scheduleRequestedMeeting'])
        ->whereNumber('requestId');
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/requests', [RequestController::class, 'adminIndex']);
    Route::get('/admin/requests/{requestId}', [RequestController::class, 'adminShow'])
        ->whereNumber('requestId');
    Route::post('/admin/requests/{requestId}/status', [RequestController::class, 'updateStatus'])
        ->whereNumber('requestId');
});
