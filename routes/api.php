<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\ScholarshipController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/applicant/register', [ApplicantController::class, 'register']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // User profile management
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
});

// Admin-only routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin management
    Route::apiResource('admins', AdminController::class);
    
    // Sponsor management (admin only)
    Route::apiResource('sponsors', UserController::class);
    Route::post('/sponsor/create', [UserController::class, 'createSponsor']);
    
    // Applicant management
    Route::apiResource('applicants', ApplicantController::class);
    
    // System management (admin only)
    Route::apiResource('countries', CountryController::class);
    Route::apiResource('universities', UniversityController::class);
    Route::apiResource('specializations', SpecializationController::class);
    Route::apiResource('scholarships', ScholarshipController::class);
});
