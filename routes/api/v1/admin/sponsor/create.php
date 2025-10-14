<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Sponsor creation endpoint
Route::post('/sponsor/create', [UserController::class, 'createSponsor']);
