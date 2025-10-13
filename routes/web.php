<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hi',function(){
return view ('hi');
});

// API Routes
Route::prefix('api')->group(function () {
    // Admin routes
    Route::post('/admin/create', [AdminController::class, 'createAdmin']);
    
    // Applicant routes
    Route::post('/applicant/register', [ApplicantController::class, 'register']);
    
    // Sponsor routes (existing)
    Route::post('/sponsor/create', [UserController::class, 'createSponsor']);
});