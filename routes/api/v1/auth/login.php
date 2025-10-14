<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Login endpoint
Route::post('/login', [AuthController::class, 'login']);
