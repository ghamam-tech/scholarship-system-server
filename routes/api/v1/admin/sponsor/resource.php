<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Sponsor resource endpoints (CRUD operations)
Route::apiResource('sponsors', UserController::class);
