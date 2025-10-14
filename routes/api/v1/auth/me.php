<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Get current user endpoint (requires authentication)
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
