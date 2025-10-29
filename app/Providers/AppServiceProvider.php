<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom route model binding for Program
        Route::bind('programId', function ($value) {
            // If it's a formatted ID like "prog_000001", extract the numeric part
            if (preg_match('/^prog_(\d+)$/', $value, $matches)) {
                return \App\Models\Program::findOrFail($matches[1]);
            }
            // If it's already a numeric ID, use it directly
            return \App\Models\Program::findOrFail($value);
        });

        // Custom route model binding for Opportunity
        Route::bind('opportunityId', function ($value) {
            // If it's a formatted ID like "opp_000001", extract the numeric part
            if (preg_match('/^opp_(\d+)$/', $value, $matches)) {
                return \App\Models\Opportunity::findOrFail($matches[1]);
            }
            // If it's already a numeric ID, use it directly
            return \App\Models\Opportunity::findOrFail($value);
        });


        // Custom route model binding for ApplicationOpportunity
        Route::bind('opportunityApplicationId', function ($value) {
            // If it's a formatted ID like "opp_000001", extract the numeric part
            if (preg_match('/^opp_(\d+)$/', $value, $matches)) {
                return \App\Models\ApplicationOpportunity::findOrFail($matches[1]);
            }
            // If it's already a numeric ID, use it directly
            return \App\Models\ApplicationOpportunity::findOrFail($value);
        });

        // Custom route model binding for ApplicationOpportunity (using applicationId in opportunity routes)
        Route::bind('applicationId', function ($value, $route) {
            // Check if this is an opportunity-related route
            if (str_contains($route->uri(), 'opportunities')) {
                // If it's a formatted ID like "opp_000001", extract the numeric part
                if (preg_match('/^opp_(\d+)$/', $value, $matches)) {
                    return \App\Models\ApplicationOpportunity::findOrFail($matches[1]);
                }
                // If it's already a numeric ID, use it directly
                return \App\Models\ApplicationOpportunity::findOrFail($value);
            }

            // For program-related routes, handle as ProgramApplication
            // If it's a formatted ID like "prog_000001", extract the numeric part
            if (preg_match('/^prog_(\d+)$/', $value, $matches)) {
                return \App\Models\ProgramApplication::findOrFail($matches[1]);
            }
            // If it's already a numeric ID, use it directly
            return \App\Models\ProgramApplication::findOrFail($value);
        });
    }
}
