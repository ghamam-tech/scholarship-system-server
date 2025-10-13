<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\UserController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/applicant/register', [ApplicantController::class, 'register']);

// Authenticated routes
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/me', [AuthController::class, 'me']);

// User profile management
Route::get('/profile', [UserController::class, 'profile']);
Route::put('/profile', [UserController::class, 'updateProfile']);
