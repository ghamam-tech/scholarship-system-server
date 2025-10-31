<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ApplicantApplication;
use App\Models\Student;
use App\Models\UserStatus;
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

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleValue = $user->role instanceof UserRole ? $user->role->value : $user->role;
        $allowedRoles = [UserRole::APPLICANT->value, UserRole::STUDENT->value];

        if (!in_array($roleValue, $allowedRoles, true)) {
            return response()->json(['message' => 'Only applicants or students can view appointments'], 403);
        }

        $userTimezone = $user->timezone ?? $this->detectUserTimezone($request);

        $meta = [
            'user_id' => $user->user_id,
            'role' => $roleValue,
        ];

        if ($roleValue === UserRole::APPLICANT->value) {
            $applicant = $user->applicant;
            if (!$applicant) {
                return response()->json(['message' => 'Applicant profile not found'], 404);
            }

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

            $meta['applicant_id'] = $applicant->applicant_id;
            $meta['application_id'] = $application->application_id;
            $meta['application_status'] = $application->currentStatus->status_name ?? null;
        } else {
            $student = Student::where('user_id', $user->user_id)->first();
            if (!$student) {
                return response()->json(['message' => 'Student profile not found'], 404);
            }

            $meta['student_id'] = $student->student_id;
        }

        $appointments = Appointment::available()
            ->upcoming()
            ->orderBy('starts_at_utc')
            ->get()
            ->map(function ($appointment) use ($userTimezone) {
                $startsAtLocal = $appointment->starts_at_utc->setTimezone($userTimezone);
                $endsAtLocal = $appointment->ends_at_utc->setTimezone($userTimezone);

                return [
                    'appointment_id' => $appointment->appointment_id,
                    'starts_at_utc' => $appointment->starts_at_utc,
                    'ends_at_utc' => $appointment->ends_at_utc,
                    'starts_at_local' => $startsAtLocal->format('Y-m-d H:i:s'),
                    'ends_at_local' => $endsAtLocal->format('Y-m-d H:i:s'),
                    'starts_at_display' => $startsAtLocal->format('M j, Y g:i A'),
                    'ends_at_display' => $endsAtLocal->format('M j, Y g:i A'),
                    'duration_min' => $appointment->duration_min,
                    'owner_timezone' => $appointment->owner_timezone,
                    'applicant_timezone' => $userTimezone,
                    'meeting_url' => $appointment->meeting_url,
                    'status' => $appointment->status,
                ];
            });

        $hasBookedAppointment = Appointment::where('user_id', $user->user_id)
            ->where('status', 'booked')
            ->where('starts_at_utc', '>', now())
            ->exists();

        $meta['total'] = $appointments->count();
        $meta['has_booked_appointment'] = $hasBookedAppointment;
        $meta['booking_rule'] = 'Each user can only have one appointment at a time';

        return response()->json([
            'data' => $appointments,
            'meta' => $meta,
        ]);
    }

    /**
     * Book an appointment
     */
    public function bookAppointment(Request $request, $appointmentId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleValue = $user->role instanceof UserRole ? $user->role->value : $user->role;
        $allowedRoles = [UserRole::APPLICANT->value, UserRole::STUDENT->value];

        if (!in_array($roleValue, $allowedRoles, true)) {
            return response()->json(['message' => 'Only applicants or students can book appointments'], 403);
        }

        $isApplicant = $roleValue === UserRole::APPLICANT->value;

        if ($isApplicant) {
            $applicant = $user->applicant;
            if (!$applicant) {
                return response()->json(['message' => 'Applicant profile not found'], 404);
            }

            $hasFirstApproval = UserStatus::where('user_id', $user->user_id)
                ->where('status_name', ApplicationStatus::FIRST_APPROVAL->value)
                ->exists();

            if (!$hasFirstApproval) {
                return response()->json([
                    'message' => 'You must have first approval status to book appointments'
                ], 403);
            }
        } else {
            $student = Student::where('user_id', $user->user_id)->first();
            if (!$student) {
                return response()->json(['message' => 'Student profile not found'], 404);
            }
        }

        $existingAppointment = Appointment::where('user_id', $user->user_id)
            ->where('status', 'booked')
            ->where('starts_at_utc', '>', now())
            ->first();

        if ($existingAppointment) {
            $viewerTimezone = $user->timezone ?? $this->detectUserTimezone($request);
            $startsAtLocal = $existingAppointment->starts_at_utc->setTimezone($viewerTimezone);
            $endsAtLocal = $existingAppointment->ends_at_utc->setTimezone($viewerTimezone);

            return response()->json([
                'message' => 'You already have a booked appointment. Each user can only have one appointment at a time.',
                'existing_appointment' => [
                    'appointment_id' => $existingAppointment->appointment_id,
                    'starts_at_utc' => $existingAppointment->starts_at_utc,
                    'ends_at_utc' => $existingAppointment->ends_at_utc,
                    'starts_at_local' => $startsAtLocal->format('Y-m-d H:i:s'),
                    'ends_at_local' => $endsAtLocal->format('Y-m-d H:i:s'),
                    'starts_at_display' => $startsAtLocal->format('M j, Y g:i A'),
                    'ends_at_display' => $endsAtLocal->format('M j, Y g:i A'),
                    'duration_min' => $existingAppointment->duration_min,
                    'meeting_url' => $existingAppointment->meeting_url,
                ]
            ], 422);
        }

        $appointment = Appointment::findOrFail($appointmentId);

        if (!$appointment->canBeBooked()) {
            return response()->json([
                'message' => 'This appointment is not available for booking'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $appointment->update([
                'status' => 'booked',
                'user_id' => $user->user_id,
                'booked_at' => now(),
            ]);

            $applicationStatusUpdated = null;

            if ($isApplicant) {
                UserStatus::create([
                    'user_id' => $user->user_id,
                    'status_name' => ApplicationStatus::MEETING_SCHEDULED->value,
                    'date' => now(),
                    'comment' => 'Appointment booked for meeting',
                ]);

                $applicationStatusUpdated = ApplicationStatus::MEETING_SCHEDULED->value;
            }

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
                'application_status_updated' => $applicationStatusUpdated,
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
     * Get all appointments for the applicant (including past ones)
     */
    public function getMyAllAppointments(Request $request)
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

        // Get applicant's timezone (automatic detection)
        $applicantTimezone = $user->timezone ?? $this->detectUserTimezone($request);

        // Get all appointments for this applicant
        $appointments = Appointment::where('user_id', $user->user_id)
            ->orderBy('starts_at_utc', 'desc')
            ->get()
            ->map(function ($appointment) use ($applicantTimezone) {
                // Convert UTC times to applicant's local timezone
                $startsAtLocal = $appointment->starts_at_utc->setTimezone($applicantTimezone);
                $endsAtLocal = $appointment->ends_at_utc->setTimezone($applicantTimezone);

                return [
                    'appointment_id' => $appointment->appointment_id,
                    'starts_at_utc' => $appointment->starts_at_utc,
                    'ends_at_utc' => $appointment->ends_at_utc,
                    'starts_at_local' => $startsAtLocal->format('Y-m-d H:i:s'),
                    'ends_at_local' => $endsAtLocal->format('Y-m-d H:i:s'),
                    'starts_at_display' => $startsAtLocal->format('M j, Y g:i A'),
                    'ends_at_display' => $endsAtLocal->format('M j, Y g:i A'),
                    'duration_min' => $appointment->duration_min,
                    'owner_timezone' => $appointment->owner_timezone,
                    'applicant_timezone' => $applicantTimezone,
                    'meeting_url' => $appointment->meeting_url,
                    'status' => $appointment->status,
                    'booked_at' => $appointment->booked_at,
                    'canceled_at' => $appointment->canceled_at,
                    'is_past' => $appointment->starts_at_utc < now(),
                    'is_future' => $appointment->starts_at_utc > now(),
                ];
            });

        return response()->json([
            'data' => $appointments,
            'meta' => [
                'user_id' => $user->user_id,
                'applicant_id' => $applicant->applicant_id,
                'total_appointments' => $appointments->count(),
                'booked_appointments' => $appointments->where('status', 'booked')->count(),
                'canceled_appointments' => $appointments->where('status', 'canceled')->count(),
                'past_appointments' => $appointments->where('is_past', true)->count(),
                'future_appointments' => $appointments->where('is_future', true)->count(),
                'booking_rule' => 'Each applicant can only have one appointment at a time',
            ]
        ]);
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

        // Get applicant's timezone (automatic detection)
        $applicantTimezone = $user->timezone ?? $this->detectUserTimezone($request);

        // Get applicant's booked appointment (only one allowed per applicant)
        $appointment = Appointment::where('user_id', $user->user_id)
            ->where('status', 'booked')
            ->where('starts_at_utc', '>', now())
            ->first();

        if (!$appointment) {
            return response()->json([
                'message' => 'No booked appointment found. You can book one appointment at a time.'
            ], 404);
        }

        // Convert UTC times to applicant's local timezone
        $startsAtLocal = $appointment->starts_at_utc->setTimezone($applicantTimezone);
        $endsAtLocal = $appointment->ends_at_utc->setTimezone($applicantTimezone);

        return response()->json([
            'data' => [
                'appointment_id' => $appointment->appointment_id,
                'starts_at_utc' => $appointment->starts_at_utc,
                'ends_at_utc' => $appointment->ends_at_utc,
                'starts_at_local' => $startsAtLocal->format('Y-m-d H:i:s'),
                'ends_at_local' => $endsAtLocal->format('Y-m-d H:i:s'),
                'starts_at_display' => $startsAtLocal->format('M j, Y g:i A'),
                'ends_at_display' => $endsAtLocal->format('M j, Y g:i A'),
                'duration_min' => $appointment->duration_min,
                'owner_timezone' => $appointment->owner_timezone,
                'applicant_timezone' => $applicantTimezone,
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

        if (!$user || $user->role !== UserRole::APPLICANT) {
            return response()->json(['message' => 'Only applicants can cancel their appointments'], 403);
        }

        $applicant = $user->applicant;
        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $appointment = Appointment::where('appointment_id', $appointmentId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        if (!$appointment->canBeCanceled()) {
            return response()->json(['message' => 'This appointment cannot be canceled'], 422);
        }

        try {
            DB::beginTransaction();

            // release the slot
            $appointment->update([
                'status' => 'available',
                'user_id' => null,
                'booked_at' => null,
                'canceled_at' => now(),
            ]);

            // only roll status back if the user had "meeting_scheduled"
            $hadMeeting = UserStatus::where('user_id', $user->user_id)
                ->where('status_name', ApplicationStatus::MEETING_SCHEDULED->value)
                ->exists();

            $statusUpdated = null;
            if ($hadMeeting) {
                UserStatus::create([
                    'user_id' => $user->user_id,
                    'status_name' => ApplicationStatus::FIRST_APPROVAL->value,
                    'date' => now(),
                    'comment' => 'Appointment canceled, back to first approval',
                ]);
                $statusUpdated = ApplicationStatus::FIRST_APPROVAL->value;
            }

            DB::commit();

            return response()->json([
                'message' => 'Appointment canceled successfully',
                'appointment' => [
                    'appointment_id' => $appointment->appointment_id,
                    'status' => $appointment->status,
                    'canceled_at' => $appointment->canceled_at,
                ],
                'application_status_updated' => $statusUpdated, // may be null if no rollback
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
     * Admin: Generate multiple appointments by splitting a window by duration
     * Body:
     * {
     *   "date": "2025-10-15",
     *   "window_start": "09:00 AM",
     *   "window_end":   "12:00 PM",
     *   "duration_min": 30,
     *   "meeting_url":  "https://meet.google.com/xxx",
     *   "owner_timezone": "Asia/Kuala_Lumpur" // optional, will fallback
     * }
     */
    public function generateAppointments(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can generate appointments'], 403);
        }

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'window_start' => ['required', 'string', 'max:20'],
            'window_end' => ['required', 'string', 'max:20'],
            'duration_min' => ['required', 'integer', 'min:5', 'max:240'],
            'meeting_url' => ['nullable', 'string', 'max:2048'],
            'owner_timezone' => ['nullable', 'string', 'max:64'],
        ]);

        // Use admin's timezone for appointment creation (automatic detection)
        $tz = $data['owner_timezone'] ?: $this->detectUserTimezone($request);

        $startLocal = $this->parseLocal($data['date'], $data['window_start'], $tz);
        $endLocal = $this->parseLocal($data['date'], $data['window_end'], $tz);

        if (!$startLocal || !$endLocal) {
            return response()->json(['message' => 'Invalid time format (use HH:mm or hh:mm AM/PM).'], 422);
        }
        if ($endLocal->lessThanOrEqualTo($startLocal)) {
            return response()->json(['message' => 'End time must be after start time.'], 422);
        }

        $duration = (int) $data['duration_min'];
        $cursor = $startLocal->copy();
        $created = 0;

        try {
            DB::beginTransaction();

            while ($cursor->lt($endLocal)) {
                $slotStartLocal = $cursor->copy();
                $slotEndLocal = $cursor->copy()->addMinutes($duration);
                if ($slotEndLocal->gt($endLocal)) {
                    break; // avoid partial slot at the end
                }

                // Respect unique(starts_at_utc)
                $exists = Appointment::where('starts_at_utc', $slotStartLocal->clone()->utc())->exists();
                if (!$exists) {
                    Appointment::create([
                        'starts_at_utc' => $slotStartLocal->clone()->utc(),
                        'ends_at_utc' => $slotEndLocal->clone()->utc(),
                        'owner_timezone' => $tz,
                        'duration_min' => $duration,
                        'meeting_url' => $data['meeting_url'] ?? null,
                        'status' => 'available',
                    ]);
                    $created++;
                }

                $cursor->addMinutes($duration);
            }

            DB::commit();

            return response()->json([
                'message' => 'Appointments generated successfully',
                'count' => $created,
                'date' => $data['date'],
                'window_start' => $data['window_start'],
                'window_end' => $data['window_end'],
                'duration_min' => $duration,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to generate appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Add one custom appointment
     * Body:
     * {
     *   "date": "2025-10-15",
     *   "time": "10:45 AM",
     *   "duration_min": 30,
     *   "meeting_url": "https://meet.google.com/xxx",
     *   "owner_timezone": "Asia/Kuala_Lumpur"
     * }
     */
    public function addCustomAppointment(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can add custom appointments'], 403);
        }

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string', 'max:20'],
            'duration_min' => ['required', 'integer', 'min:5', 'max:240'],
            'meeting_url' => ['nullable', 'string', 'max:2048'],
            'owner_timezone' => ['nullable', 'string', 'max:64'],
        ]);

        // Use admin's timezone for appointment creation (automatic detection)
        $tz = $data['owner_timezone'] ?: $this->detectUserTimezone($request);

        $startLocal = $this->parseLocal($data['date'], $data['time'], $tz);
        if (!$startLocal) {
            return response()->json(['message' => 'Invalid time format (use HH:mm or hh:mm AM/PM).'], 422);
        }
        $endLocal = $startLocal->copy()->addMinutes((int) $data['duration_min']);

        try {
            $appointment = Appointment::firstOrCreate(
                ['starts_at_utc' => $startLocal->clone()->utc()],
                [
                    'ends_at_utc' => $endLocal->clone()->utc(),
                    'owner_timezone' => $tz,
                    'duration_min' => (int) $data['duration_min'],
                    'meeting_url' => $data['meeting_url'] ?? null,
                    'status' => 'available',
                ]
            );

            return response()->json([
                'message' => 'Custom appointment added successfully',
                'appointment' => $appointment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add custom appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set user timezone
     */
    public function setTimezone(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $data = $request->validate([
            'timezone' => ['required', 'string', 'max:64'],
        ]);

        // Validate timezone
        if (!$this->isValidTimezone($data['timezone'])) {
            return response()->json(['message' => 'Invalid timezone'], 422);
        }

        $user->update(['timezone' => $data['timezone']]);

        return response()->json([
            'message' => 'Timezone updated successfully',
            'timezone' => $user->timezone,
        ]);
    }

    /**
     * Auto-detect and set user timezone
     */
    public function autoDetectTimezone(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Detect timezone automatically
        $detectedTimezone = $this->detectUserTimezone($request);

        // Update user's timezone if not already set
        if (!$user->timezone) {
            $user->update(['timezone' => $detectedTimezone]);
        }

        return response()->json([
            'message' => 'Timezone auto-detected successfully',
            'detected_timezone' => $detectedTimezone,
            'user_timezone' => $user->timezone,
            'was_updated' => !$user->timezone,
        ]);
    }

    /**
     * Automatically detect user's timezone from request headers or IP
     */
    private function detectUserTimezone(Request $request): string
    {
        // 1. Try to get timezone from request header (set by frontend)
        $timezoneFromHeader = $request->header('X-User-Timezone');
        if ($timezoneFromHeader && $this->isValidTimezone($timezoneFromHeader)) {
            return $timezoneFromHeader;
        }

        // 2. Try to get timezone from request body (if provided)
        $timezoneFromBody = $request->input('timezone');
        if ($timezoneFromBody && $this->isValidTimezone($timezoneFromBody)) {
            return $timezoneFromBody;
        }

        // 3. Try to detect from IP geolocation (fallback)
        $ipTimezone = $this->detectTimezoneFromIP($request->ip());
        if ($ipTimezone) {
            return $ipTimezone;
        }

        // 4. Final fallback to UTC
        return 'UTC';
    }

    /**
     * Validate if timezone string is valid
     */
    private function isValidTimezone(string $timezone): bool
    {
        try {
            new \DateTimeZone($timezone);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Detect timezone from IP address (simplified version)
     */
    private function detectTimezoneFromIP(string $ip): ?string
    {
        // Common timezone mappings for major regions
        $timezoneMap = [
            // Saudi Arabia
            'SA' => 'Asia/Riyadh',
            // Malaysia
            'MY' => 'Asia/Kuala_Lumpur',
            // UAE
            'AE' => 'Asia/Dubai',
            // Egypt
            'EG' => 'Africa/Cairo',
            // Jordan
            'JO' => 'Asia/Amman',
            // Kuwait
            'KW' => 'Asia/Kuwait',
            // Qatar
            'QA' => 'Asia/Qatar',
            // Bahrain
            'BH' => 'Asia/Bahrain',
            // Oman
            'OM' => 'Asia/Muscat',
            // Turkey
            'TR' => 'Europe/Istanbul',
            // Pakistan
            'PK' => 'Asia/Karachi',
            // India
            'IN' => 'Asia/Kolkata',
            // Indonesia
            'ID' => 'Asia/Jakarta',
            // Singapore
            'SG' => 'Asia/Singapore',
            // Thailand
            'TH' => 'Asia/Bangkok',
            // Philippines
            'PH' => 'Asia/Manila',
            // Vietnam
            'VN' => 'Asia/Ho_Chi_Minh',
            // China
            'CN' => 'Asia/Shanghai',
            // Japan
            'JP' => 'Asia/Tokyo',
            // South Korea
            'KR' => 'Asia/Seoul',
            // Australia
            'AU' => 'Australia/Sydney',
            // New Zealand
            'NZ' => 'Pacific/Auckland',
            // UK
            'GB' => 'Europe/London',
            // Germany
            'DE' => 'Europe/Berlin',
            // France
            'FR' => 'Europe/Paris',
            // Italy
            'IT' => 'Europe/Rome',
            // Spain
            'ES' => 'Europe/Madrid',
            // Netherlands
            'NL' => 'Europe/Amsterdam',
            // USA
            'US' => 'America/New_York',
            // Canada
            'CA' => 'America/Toronto',
            // Brazil
            'BR' => 'America/Sao_Paulo',
            // Argentina
            'AR' => 'America/Argentina/Buenos_Aires',
            // Mexico
            'MX' => 'America/Mexico_City',
            // South Africa
            'ZA' => 'Africa/Johannesburg',
            // Nigeria
            'NG' => 'Africa/Lagos',
            // Kenya
            'KE' => 'Africa/Nairobi',
            // Morocco
            'MA' => 'Africa/Casablanca',
            // Algeria
            'DZ' => 'Africa/Algiers',
            // Tunisia
            'TN' => 'Africa/Tunis',
        ];

        // For now, return null to use UTC fallback
        // In production, you could integrate with a geolocation service
        // like ipapi.co, ipinfo.io, or MaxMind GeoIP2
        return null;
    }

    /** Helper: parse local wall-clock time into Carbon */
    private function parseLocal(string $date, string $time, string $tz): ?Carbon
    {
        $formats = ['H:i', 'G:i', 'h:i A', 'g:i A', 'h:i a', 'g:i a'];
        foreach ($formats as $fmt) {
            try {
                $c = Carbon::createFromFormat("Y-m-d {$fmt}", "{$date} {$time}", $tz);
                if ($c !== false)
                    return $c;
            } catch (\Throwable $e) { /* try next */
            }
        }
        return null;
    }

    /**
     * Admin: Get appointments by date
     */
    public function getAppointmentsByDate(Request $request, $date)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view appointments by date'], 403);
        }

        // Validate date format
        try {
            $date = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }

        // Get admin's timezone (automatic detection)
        $adminTimezone = $user->timezone ?? $this->detectUserTimezone($request);

        $appointments = Appointment::with('user')
            ->whereDate('starts_at_utc', $date)
            ->orderBy('starts_at_utc')
            ->get()
            ->map(function ($appointment) use ($adminTimezone) {
                // Convert UTC times to admin's local timezone
                $startsAtLocal = $appointment->starts_at_utc->setTimezone($adminTimezone);
                $endsAtLocal = $appointment->ends_at_utc->setTimezone($adminTimezone);

                return [
                    'appointment_id' => $appointment->appointment_id,
                    'starts_at_utc' => $appointment->starts_at_utc,
                    'ends_at_utc' => $appointment->ends_at_utc,
                    'starts_at_local' => $startsAtLocal->format('Y-m-d H:i:s'),
                    'ends_at_local' => $endsAtLocal->format('Y-m-d H:i:s'),
                    'starts_at_display' => $startsAtLocal->format('M j, Y g:i A'),
                    'ends_at_display' => $endsAtLocal->format('M j, Y g:i A'),
                    'duration_min' => $appointment->duration_min,
                    'owner_timezone' => $appointment->owner_timezone,
                    'admin_timezone' => $adminTimezone,
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
                            'phone' => $appointment->user->applicant->phone,
                        ] : null,
                    ] : null,
                    'booked_at' => $appointment->booked_at,
                    'canceled_at' => $appointment->canceled_at,
                ];
            });

        return response()->json([
            'data' => $appointments,
            'meta' => [
                'date' => $date->format('Y-m-d'),
                'total' => $appointments->count(),
                'available' => $appointments->where('status', 'available')->count(),
                'booked' => $appointments->where('status', 'booked')->count(),
                'canceled' => $appointments->where('status', 'canceled')->count(),
            ]
        ]);
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
