<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicantController;

Route::apiResource('applicant', ApplicantController::class);
