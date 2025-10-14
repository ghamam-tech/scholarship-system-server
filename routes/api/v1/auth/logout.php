<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Logout endpoint (requires authentication)
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
