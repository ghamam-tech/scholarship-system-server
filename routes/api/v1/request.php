<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/student/requests', [RequestController::class, 'index']);
    Route::post('/student/requests', [RequestController::class, 'store']);
    Route::get('/student/requests/{requestId}', [RequestController::class, 'show'])
        ->whereNumber('requestId');
});
