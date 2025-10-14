<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UniversityController;

Route::apiResource('university', UniversityController::class);
