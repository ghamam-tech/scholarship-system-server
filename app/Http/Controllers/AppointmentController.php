<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ApplicantApplication;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Get available appointments for applicants with first_approval status
     */
    public function getAvailableAppointments(Request $request)
    {
        $user = $request->user();

        // Check if user is an applicant
        if (!$user || $user->role !== UserRole::APPLICANT) {
            return response()->json(['message' => 'Only applicants can view appointments'], 403);
        }

        $applicant = $user->applicant;
        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        // Check if applicant has an application with first_approval status
        $application = $applicant->applications()
            ->whereHas('currentStatus', function ($query) {
                $query->where('status_name', ApplicationStatus::FIRST_APPROVAL->value);
            })
            ->first();

        if (!$application) {
            return response()->json([
                'message' => 'You must have an application with first approval status to view appointments'
            ], 403);
        }

        // Get available appointments (not booked, not canceled, and in the future)
        $appointments = Appointment::available()
            ->upcoming()
            ->orderBy('starts_at_utc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'starts_at_utc' => $appointment->starts_at_utc,
                    'ends_at_utc' => $appointment->ends_at_utc,
                    'duration_min' => $appointment->duration_min,
                    'owner_timezone' => $appointment->owner_timezone,
                    'meeting_url' => $appointment->meeting_url,
                    'status' => $appointment->status,
                ];
            });

        return response()->json([
            'data' => $appointments,
            'meta' => [
                'total' => $appointments->count(),
                'user_id' => $user->user_id,
                'applicant_id' => $applicant->applicant_id,
                'application_id' => $application->application_id,
                'application_status' => $application->currentStatus->status_name,
            ]
        ]);
    }

    /**
     * Book an appointment
     */
    public function bookAppointment(Request $request, $appointmentId)
    {
        $user = $request->user();

        // Check if user is an applicant
        if (!$user || $user->role !== UserRole::APPLICANT) {
            return response()->json(['message' => 'Only applicants can book appointments'], 403);
        }

        $applicant = $user->applicant;
        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        // Check if applicant has an application with first_approval status
        $application = $applicant->applications()
            ->whereHas('currentStatus', function ($query) {
                $query->where('status_name', ApplicationStatus::FIRST_APPROVAL->value);
            })
            ->first();

        if (!$application) {
            return response()->json([
                'message' => 'You must have an application with first approval status to book appointments'
            ], 403);
        }

        // Check if applicant already has a booked appointment
        $existingAppointment = Appointment::where('user_id', $user->user_id)
            ->where('status', 'booked')
            ->where('starts_at_utc', '>', now())
            ->first();

        if ($existingAppointment) {
            return response()->json([
                'message' => 'You already have a booked appointment',
                'existing_appointment' => [
                    'appointment_id' => $existingAppointment->appointment_id,
                    'starts_at_utc' => $existingAppointment->starts_at_utc,
                    'ends_at_utc' => $existingAppointment->ends_at_utc,
                ]
            ], 422);
        }

        // Find the appointment
        $appointment = Appointment::findOrFail($appointmentId);

        // Check if appointment can be booked
        if (!$appointment->canBeBooked()) {
            return response()->json([
                'message' => 'This appointment is not available for booking'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Book the appointment
            $appointment->update([
                'status' => 'booked',
                'user_id' => $user->user_id,
                'booked_at' => now(),
            ]);

            // Update application status to meeting_scheduled
            $application->statuses()->create([
                'status_name' => ApplicationStatus::MEETING_SCHEDULED->value,
                'date' => now(),
                'comment' => 'Appointment booked for meeting',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Appointment booked successfully',
                'appointment' => [
                    'appointment_id' => $appointment->appointment_id,
                    'starts_at_utc' => $appointment->starts_at_utc,
                    'ends_at_utc' => $appointment->ends_at_utc,
                    'duration_min' => $appointment->duration_min,
                    'owner_timezone' => $appointment->owner_timezone,
                    'meeting_url' => $appointment->meeting_url,
                    'status' => $appointment->status,
                    'booked_at' => $appointment->booked_at,
                ],
                'application_status_updated' => ApplicationStatus::MEETING_SCHEDULED->value,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to book appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get applicant's booked appointment
     */
    public function getMyAppointment(Request $request)
    {
        $user = $request->user();

        // Check if user is an applicant
        if (!$user || $user->role !== UserRole::APPLICANT) {
            return response()->json(['message' => 'Only applicants can view their appointments'], 403);
        }

        $applicant = $user->applicant;
        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        // Get applicant's booked appointment
        $appointment = Appointment::where('user_id', $user->user_id)
            ->where('status', 'booked')
            ->where('starts_at_utc', '>', now())
            ->first();

        if (!$appointment) {
            return response()->json([
                'message' => 'No booked appointment found'
            ], 404);
        }

        return response()->json([
            'data' => [
                'appointment_id' => $appointment->appointment_id,
                'starts_at_utc' => $appointment->starts_at_utc,
                'ends_at_utc' => $appointment->ends_at_utc,
                'duration_min' => $appointment->duration_min,
                'owner_timezone' => $appointment->owner_timezone,
                'meeting_url' => $appointment->meeting_url,
                'status' => $appointment->status,
                'booked_at' => $appointment->booked_at,
            ],
            'meta' => [
                'user_id' => $user->user_id,
                'applicant_id' => $applicant->applicant_id,
            ]
        ]);
    }

    /**
     * Cancel applicant's booked appointment
     */
    public function cancelAppointment(Request $request, $appointmentId)
    {
        $user = $request->user();

        // Check if user is an applicant
        if (!$user || $user->role !== UserRole::APPLICANT) {
            return response()->json(['message' => 'Only applicants can cancel their appointments'], 403);
        }

        $applicant = $user->applicant;
        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        // Find the appointment
        $appointment = Appointment::where('appointment_id', $appointmentId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        // Check if appointment can be canceled
        if (!$appointment->canBeCanceled()) {
            return response()->json([
                'message' => 'This appointment cannot be canceled'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Cancel the appointment
            $appointment->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);

            // Update application status back to first_approval
            $application = $applicant->applications()
                ->whereHas('currentStatus', function ($query) {
                    $query->where('status_name', ApplicationStatus::MEETING_SCHEDULED->value);
                })
                ->first();

            if ($application) {
                $application->statuses()->create([
                    'status_name' => ApplicationStatus::FIRST_APPROVAL->value,
                    'date' => now(),
                    'comment' => 'Appointment canceled, back to first approval',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Appointment canceled successfully',
                'appointment' => [
                    'appointment_id' => $appointment->appointment_id,
                    'status' => $appointment->status,
                    'canceled_at' => $appointment->canceled_at,
                ],
                'application_status_updated' => ApplicationStatus::FIRST_APPROVAL->value,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Create new appointment slots
     */
    public function createAppointment(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can create appointments'], 403);
        }

        $data = $request->validate([
            'starts_at_utc' => ['required', 'date', 'after:now'],
            'ends_at_utc' => ['required', 'date', 'after:starts_at_utc'],
            'owner_timezone' => ['required', 'string', 'max:64'],
            'duration_min' => ['required', 'integer', 'min:15', 'max:480'], // 15 minutes to 8 hours
            'meeting_url' => ['nullable', 'string', 'max:2048'],
        ]);

        try {
            $appointment = Appointment::create($data);

            return response()->json([
                'message' => 'Appointment created successfully',
                'appointment' => $appointment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all appointments
     */
    public function getAllAppointments(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view all appointments'], 403);
        }

        $appointments = Appointment::with('user')
            ->orderBy('starts_at_utc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'starts_at_utc' => $appointment->starts_at_utc,
                    'ends_at_utc' => $appointment->ends_at_utc,
                    'duration_min' => $appointment->duration_min,
                    'owner_timezone' => $appointment->owner_timezone,
                    'meeting_url' => $appointment->meeting_url,
                    'status' => $appointment->status,
                    'user_id' => $appointment->user_id,
                    'user' => $appointment->user ? [
                        'user_id' => $appointment->user->user_id,
                        'email' => $appointment->user->email,
                        'applicant' => $appointment->user->applicant ? [
                            'applicant_id' => $appointment->user->applicant->applicant_id,
                            'en_name' => $appointment->user->applicant->en_name,
                            'ar_name' => $appointment->user->applicant->ar_name,
                        ] : null,
                    ] : null,
                    'booked_at' => $appointment->booked_at,
                    'canceled_at' => $appointment->canceled_at,
                ];
            });

        return response()->json([
            'data' => $appointments,
            'meta' => [
                'total' => $appointments->count(),
                'available' => $appointments->where('status', 'available')->count(),
                'booked' => $appointments->where('status', 'booked')->count(),
                'canceled' => $appointments->where('status', 'canceled')->count(),
            ]
        ]);
    }

    /**
     * Admin: Update appointment
     */
    public function updateAppointment(Request $request, $appointmentId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can update appointments'], 403);
        }

        $appointment = Appointment::findOrFail($appointmentId);

        $data = $request->validate([
            'starts_at_utc' => ['sometimes', 'date', 'after:now'],
            'ends_at_utc' => ['sometimes', 'date', 'after:starts_at_utc'],
            'owner_timezone' => ['sometimes', 'string', 'max:64'],
            'duration_min' => ['sometimes', 'integer', 'min:15', 'max:480'],
            'meeting_url' => ['nullable', 'string', 'max:2048'],
            'status' => ['sometimes', 'in:available,booked,canceled'],
        ]);

        try {
            $appointment->update($data);

            return response()->json([
                'message' => 'Appointment updated successfully',
                'appointment' => $appointment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Delete appointment
     */
    public function deleteAppointment(Request $request, $appointmentId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can delete appointments'], 403);
        }

        $appointment = Appointment::findOrFail($appointmentId);

        // Check if appointment is booked
        if ($appointment->isBooked()) {
            return response()->json([
                'message' => 'Cannot delete a booked appointment. Cancel it first.'
            ], 422);
        }

        try {
            $appointment->delete();

            return response()->json([
                'message' => 'Appointment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
