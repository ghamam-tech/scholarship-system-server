<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::apiResource('admins', AdminController::class);
