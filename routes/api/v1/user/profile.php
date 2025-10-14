<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// User profile management endpoints (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
});
