<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

/*
|--------------------------------------------------------------------------
| Appointment Routes
|--------------------------------------------------------------------------
|
| Applicant routes: View and book appointments (requires first_approval status)
| Admin routes: Manage all appointments
|
*/

// Applicant routes - require authentication and first_approval status
Route::middleware(['auth:sanctum', 'role:applicant'])->group(function () {
    // Get available appointments (only for applicants with first_approval status)
    Route::get('appointments/available', [AppointmentController::class, 'getAvailableAppointments']);

    // Book an appointment
    Route::post('appointments/{appointment}/book', [AppointmentController::class, 'bookAppointment']);

    // Get my booked appointment
    Route::get('appointments/my-appointment', [AppointmentController::class, 'getMyAppointment']);

    // Cancel my appointment
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancelAppointment']);
});

// Admin routes - require authentication and admin role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Get all appointments
    Route::get('admin/appointments', [AppointmentController::class, 'getAllAppointments']);

    // Create new appointment
    Route::post('admin/appointments', [AppointmentController::class, 'createAppointment']);

    // Update appointment
    Route::match(['put', 'patch'], 'admin/appointments/{appointment}', [AppointmentController::class, 'updateAppointment']);

    // Delete appointment
    Route::delete('admin/appointments/{appointment}', [AppointmentController::class, 'deleteAppointment']);
});
