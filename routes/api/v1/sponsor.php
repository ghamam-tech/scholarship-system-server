<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SponsorController;
use App\Models\Sponsor;

/*
|--------------------------------------------------------------------------
| Public sponsor routes (for applicants and unauthenticated users)
|--------------------------------------------------------------------------
*/

Route::get('sponsors', [SponsorController::class, 'index']);
Route::get('sponsors/{sponsor}', [SponsorController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Sponsor-only routes  
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:sponsor'])
    ->prefix('sponsor')
    ->group(function () {
        Route::get('profile', function (Request $request) {
            // Sponsor can view their own profile via a dedicated route
            $sponsor = Sponsor::where('user_id', $request->user()->user_id)->first();
            if (!$sponsor) {
                return response()->json(['message' => 'Sponsor profile not found'], 404);
            }
            return response()->json($sponsor->load('user'));
        });

        // Sponsors can update their own profile
        Route::match(['put', 'patch'], 'profile', function (Request $request) {
            $sponsor = Sponsor::where('user_id', $request->user()->user_id)->first();
            if (!$sponsor) {
                return response()->json(['message' => 'Sponsor profile not found'], 404);
            }
            return app(SponsorController::class)->update($request, $sponsor);
        });
    });

/*
|--------------------------------------------------------------------------
| Admin-only sponsor routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('sponsors', [SponsorController::class, 'index']); // Now shows ALL sponsors
        Route::post('sponsors', [SponsorController::class, 'store']);
        Route::match(['put', 'patch'], 'sponsors/{sponsor}', [SponsorController::class, 'update']);
        Route::delete('sponsors/{sponsor}', [SponsorController::class, 'destroy']);
    });


// Add this to your routes file
Route::middleware(['auth:sanctum', 'role:sponsor'])
    ->get('sponsor/profile', function (Request $request) {
        $sponsor = Sponsor::where('user_id', $request->user()->user_id)->first();
        if (!$sponsor) {
            return response()->json(['message' => 'Sponsor profile not found'], 404);
        }
        return response()->json($sponsor->load('user'));
    });
