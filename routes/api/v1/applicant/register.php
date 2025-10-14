<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantController;

// Applicant registration endpoint
Route::post('/applicant/register', [ApplicantController::class, 'register']);
