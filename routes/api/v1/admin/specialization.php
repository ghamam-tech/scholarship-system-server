<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpecializationController;

Route::apiResource('specialization', SpecializationController::class);
