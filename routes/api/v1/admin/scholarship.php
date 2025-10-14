<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScholarshipController;

Route::apiResource('scholarship', ScholarshipController::class);
