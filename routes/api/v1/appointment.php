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

    // Get all my appointments (including past ones)
    Route::get('appointments/my-all-appointments', [AppointmentController::class, 'getMyAllAppointments']);

    // Cancel my appointment
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancelAppointment']);
});

// Common routes for all authenticated users
Route::middleware(['auth:sanctum'])->group(function () {
    // Set user timezone
    Route::post('timezone', [AppointmentController::class, 'setTimezone']);

    // Auto-detect user timezone
    Route::post('timezone/auto-detect', [AppointmentController::class, 'autoDetectTimezone']);
});

// Admin routes - require authentication and admin role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Get all appointments
    Route::get('admin/appointments', [AppointmentController::class, 'getAllAppointments']);

    // Get appointments by specific date
    Route::get('admin/appointments/date/{date}', [AppointmentController::class, 'getAppointmentsByDate']);

    // Generate multiple appointments (bulk creation)
    Route::post('admin/appointments/generate', [AppointmentController::class, 'generateAppointments']);

    // Add custom single appointment
    Route::post('admin/appointments/custom', [AppointmentController::class, 'addCustomAppointment']);

    // Create new appointment (legacy method)
    Route::post('admin/appointments', [AppointmentController::class, 'createAppointment']);

    // Update appointment
    Route::match(['put', 'patch'], 'admin/appointments/{appointment}', [AppointmentController::class, 'updateAppointment']);

    // Delete appointment
    Route::delete('admin/appointments/{appointment}', [AppointmentController::class, 'deleteAppointment']);
});
