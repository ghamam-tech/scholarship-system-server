<?php

use App\Http\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/announcements', [AnnouncementController::class, 'adminIndex']);
        Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::match(['put', 'patch'], '/announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish']);
        Route::post('/announcements/{announcement}/republish', [AnnouncementController::class, 'republish']);
    });

    Route::middleware(['role:student'])->group(function () {
        Route::get('/student/announcements', [AnnouncementController::class, 'active']);
    });
});
