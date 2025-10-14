<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryController;

Route::apiResource('country', CountryController::class);
