<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SponsorController;

// Route::prefix('admin')
//     ->middleware(['auth:sanctum', 'role:admin'])
//     ->group(function () {
//         Route::post('sponsors', [AuthController::class, 'createSponsor']);
//         Route::apiResource('sponsors', SponsorController::class);
//         Route::match(['put', 'patch'], 'sponsors/{id}', [SponsorController::class, 'update'])
//             ->name('sponsors.update');
//     });
