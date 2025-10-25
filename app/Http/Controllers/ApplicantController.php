<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Qualification;
use App\Models\Appointment;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ApplicantController extends Controller
{
    public function completeProfile(Request $request)
    {
        Log::info($request->all());
        $user = $request->user();
        // $data = $request->all();
        // Check if applicant already exists
        $applicant = $user->applicant;
        if (!$applicant) {
            $applicant = Applicant::create(['user_id' => $user->user_id]);
        }

        $data = $request->validate([
            // Personal Info
            'personal_info.ar_name' => ['required', 'string', 'max:255'],
            'personal_info.en_name' => ['required', 'string', 'max:255'],
            'personal_info.nationality' => ['required', 'string', 'max:100'],
            'personal_info.gender' => ['required', 'string', 'in:male,female'],
            'personal_info.place_of_birth' => ['required', 'string', 'max:255'],
            'personal_info.phone' => ['required', 'string', 'max:20'],
            'personal_info.passport_number' => ['required', 'string', 'max:50', 'unique:applicants,passport_number,' . $applicant->applicant_id . ',applicant_id'],
            'personal_info.passport_expiry' => ['nullable', 'date'],
            'personal_info.date_of_birth' => ['required', 'string'],
            'personal_info.parent_contact_name' => ['required', 'string', 'max:255'],
            'personal_info.parent_contact_phone' => ['required', 'string', 'max:20'],
            'personal_info.residence_country' => ['required', 'string', 'max:100'],
            'personal_info.language' => ['required', 'string', 'max:50'],
            'personal_info.is_studied_in_saudi' => ['required', 'boolean'],
            'personal_info.tahseeli_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'personal_info.qudorat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            // Academic Info (must have at least 1 qualification)
            'academic_info.qualifications' => ['required', 'array', 'min:1'],
            'academic_info.qualifications.*.qualification_type' => ['required', Rule::in(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other'])],
            'academic_info.qualifications.*.institute_name' => ['required', 'string', 'max:255'],
            'academic_info.qualifications.*.year_of_graduation' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'academic_info.qualifications.*.cgpa' => ['nullable', 'numeric', 'min:0'],
            'academic_info.qualifications.*.cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
            'academic_info.qualifications.*.language_of_study' => ['nullable', 'string', 'max:100'],
            'academic_info.qualifications.*.specialization' => ['nullable', 'string', 'max:255'],
            'academic_info.qualifications.*.research_title' => ['nullable', 'string', 'max:500'],
            'academic_info.qualifications.*.document_file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],

            // Document Files (all required except volunteering)
            'passport_copy' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'personal_image' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'tahsili_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'qudorat_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'volunteering_certificate' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            DB::beginTransaction();

            // Step 1: Update Applicant Personal Info
            $applicant->update(array_merge($data['personal_info'], ['is_completed' => true]));

            // Step 2: Upload applicant documents
            $this->handleDocumentUploads($request, $applicant);

            // Step 3: Replace all qualifications
            $user = $request->user();
            $user->qualifications()->delete();

            foreach ($data['academic_info']['qualifications'] as $index => $qualData) {
                $documentPath = null;
                if ($request->hasFile("academic_info.qualifications.{$index}.document_file")) {
                    $file = $request->file("academic_info.qualifications.{$index}.document_file");
                    $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    // store under users/, save PATH only
                    $documentPath = $file->storeAs("users/{$user->user_id}/qualifications", $filename, 's3');
                }

                Qualification::create([
                    'user_id' => $user->user_id,
                    'qualification_type' => $qualData['qualification_type'],
                    'institute_name' => $qualData['institute_name'],
                    'year_of_graduation' => $qualData['year_of_graduation'],
                    'cgpa' => $qualData['cgpa'] ?? null,
                    'cgpa_out_of' => $qualData['cgpa_out_of'] ?? null,
                    'language_of_study' => $qualData['language_of_study'] ?? null,
                    'specialization' => $qualData['specialization'] ?? null,
                    'research_title' => $qualData['research_title'] ?? null,
                    'document_file' => $documentPath, // path (object key), not full URL
                ]);
            }

            DB::commit();

            $quals = $user->qualifications()->get()->map(function ($q) {
                $url = $q->document_file ? Storage::disk('s3')->url($q->document_file) : null;
                $q->document_url = $url;      // extra field (optional)
                $q->document_file = $url;      // overwrite for this response only
                return $q;
            });

            $applicant->setRelation('qualifications', $quals);

            // Also overwrite applicant doc fields to URLs if you want the same keys:
            $toUrl = fn($p) => $p ? Storage::disk('s3')->url($p) : null;
            $applicant->passport_copy_img = $toUrl($applicant->passport_copy_img);
            $applicant->personal_image = $toUrl($applicant->personal_image);
            $applicant->volunteering_certificate_file = $toUrl($applicant->volunteering_certificate_file);
            $applicant->tahsili_file = $toUrl($applicant->tahsili_file);
            $applicant->qudorat_file = $toUrl($applicant->qudorat_file);

            return response()->json([
                'message' => 'Profile completed successfully',
                'applicant' => $applicant,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to complete profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update applicant profile (partial update)
     */
    public function updateProfile(Request $request)
    {
        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $data = $request->validate([
            'personal_info.ar_name' => ['sometimes', 'string', 'max:255'],
            'personal_info.en_name' => ['sometimes', 'string', 'max:255'],
            'personal_info.nationality' => ['sometimes', 'string', 'max:100'],
            'personal_info.gender' => ['sometimes', 'string', 'in:male,female'],
            'personal_info.place_of_birth' => ['sometimes', 'string', 'max:255'],
            'personal_info.phone' => ['sometimes', 'string', 'max:20'],
            'personal_info.passport_number' => ['sometimes', 'string', 'max:50', 'unique:applicants,passport_number,' . $applicant->applicant_id . ',applicant_id'],
            'personal_info.date_of_birth' => ['sometimes', 'date'],
            'personal_info.parent_contact_name' => ['sometimes', 'string', 'max:255'],
            'personal_info.parent_contact_phone' => ['sometimes', 'string', 'max:20'],
            'personal_info.residence_country' => ['sometimes', 'string', 'max:100'],
            'personal_info.language' => ['sometimes', 'string', 'max:50'],
            'personal_info.is_studied_in_saudi' => ['sometimes', 'boolean'],
            'personal_info.tahseeli_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'personal_info.qudorat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            // Optional document uploads
            'passport_copy' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'personal_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'tahsili_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'qudorat_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'volunteering_certificate' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            DB::beginTransaction();

            if (isset($data['personal_info'])) {
                $applicant->update($data['personal_info']);
            }

            $this->handleDocumentUploads($request, $applicant);

            DB::commit();

            return response()->json([
                'message' => 'Profile updated successfully',
                'applicant' => $applicant->load('qualifications')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle document uploads for applicant profile
     */
    private function handleDocumentUploads(Request $request, Applicant $applicant)
    {
        $documentFields = [
            'passport_copy' => 'passport_copy_img',
            'personal_image' => 'personal_image',
            'tahsili_file' => 'tahsili_file',
            'qudorat_file' => 'qudorat_file',
            'volunteering_certificate' => 'volunteering_certificate_file',
        ];

        foreach ($documentFields as $requestField => $dbField) {
            if ($request->hasFile($requestField)) {
                if ($applicant->$dbField) {
                    Storage::disk('s3')->delete($applicant->$dbField); // delete by PATH
                }

                $file = $request->file($requestField);
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs("applicants/{$applicant->applicant_id}/documents", $filename, 's3');

                // Save PATH only
                $applicant->update([$dbField => $path]);
            }
        }
    }

    /**
     * Get applicant profile with qualifications
     */
    public function getProfile(Request $request)
    {
        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        // loading the users data to get the qualifications through user
        $applicant->load('user');
        $applicant->setRelation('qualifications', $request->user()->qualifications);

        return response()->json(['applicant' => $applicant]);
    }
    /**
     * Update applicant profile (partial update)
     */


    /**
     * Add new qualification
     */
    public function addQualification(Request $request)
    {
        $user = $request->user();

        if (!$user->applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $data = $request->validate([
            'qualification_type' => ['required', Rule::in(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other'])],
            'institute_name' => ['required', 'string', 'max:255'],
            'year_of_graduation' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'cgpa' => ['nullable', 'numeric', 'min:0'],
            'cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
            'language_of_study' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'research_title' => ['nullable', 'string', 'max:500'],
            'document_file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            $filename = time() . '_' . str_replace(' ', '_', $request->file('document_file')->getClientOriginalName());
            $documentPath = $request->file('document_file')->storeAs("users/{$user->user_id}/qualifications", $filename, 's3');

            $qualification = Qualification::create([
                'user_id' => $user->user_id,
                'qualification_type' => $data['qualification_type'],
                'institute_name' => $data['institute_name'],
                'year_of_graduation' => $data['year_of_graduation'],
                'cgpa' => $data['cgpa'] ?? null,
                'cgpa_out_of' => $data['cgpa_out_of'] ?? null,
                'language_of_study' => $data['language_of_study'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'research_title' => $data['research_title'] ?? null,
                'document_file' => $documentPath, // save PATH
            ]);

            return response()->json([
                'message' => 'Qualification added successfully',
                'qualification' => $qualification
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add qualification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update qualification
     */
    public function updateQualification(Request $request, $qualificationId)
    {
        $user = $request->user();

        if (!$user->applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $qualification = $user->qualifications()->findOrFail($qualificationId);

        $data = $request->validate([
            'qualification_type' => ['sometimes', Rule::in(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other'])],
            'institute_name' => ['sometimes', 'string', 'max:255'],
            'year_of_graduation' => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'cgpa' => ['nullable', 'numeric', 'min:0'],
            'cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
            'language_of_study' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'research_title' => ['nullable', 'string', 'max:500'],
            'document_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            $documentPath = $qualification->document_file;

            if ($request->hasFile('document_file')) {
                if ($documentPath) {
                    Storage::disk('s3')->delete($documentPath); // delete by PATH
                }
                $file = $request->file('document_file');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $documentPath = $file->storeAs("users/{$user->user_id}/qualifications", $filename, 's3');
            }

            $qualification->update(array_filter([
                'qualification_type' => $data['qualification_type'] ?? null,
                'institute_name' => $data['institute_name'] ?? null,
                'year_of_graduation' => $data['year_of_graduation'] ?? null,
                'cgpa' => array_key_exists('cgpa', $data) ? $data['cgpa'] : null,
                'cgpa_out_of' => array_key_exists('cgpa_out_of', $data) ? $data['cgpa_out_of'] : null,
                'language_of_study' => $data['language_of_study'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'research_title' => $data['research_title'] ?? null,
                'document_file' => $documentPath,
            ], static fn($v) => $v !== null));

            // $qualification->refresh(); // ensure response matches DB

            return response()->json([
                'message' => 'Qualification updated successfully',
                'qualification' => $qualification,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update qualification',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Delete qualification
     */
    public function deleteQualification(Request $request, $qualificationId)
    {
        $user = $request->user();

        if (!$user->applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $qualification = $user->qualifications()->findOrFail($qualificationId);

        try {
            if ($qualification->document_file) {
                Storage::disk('s3')->delete($qualification->document_file);
            }

            $qualification->delete();

            return response()->json(['message' => 'Qualification deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete qualification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle document uploads for applicant profile
     */


    /**
     * Display a listing of applicants (Admin only)
     */
    public function index()
    {
        $applicants = Applicant::with('user', 'qualifications', 'applications')->get();
        return response()->json($applicants);
    }

    /**
     * Display the specified applicant (Admin only)
     */
    public function show(Applicant $applicant)
    {
        $applicant->load('user', 'qualifications', 'applications');
        return response()->json($applicant);
    }

    /**
     * Remove the specified applicant (Admin only)
     */
    public function destroy(Applicant $applicant)
    {
        try {
            // Delete applicant files
            foreach (['passport_copy_img', 'personal_image', 'volunteering_certificate_file', 'tahsili_file', 'qudorat_file'] as $field) {
                if ($applicant->$field) {
                    Storage::disk('s3')->delete($applicant->$field); // ensure this stores PATHs too
                }
            }

            // Delete qualifications via the user
            $user = $applicant->user;
            if ($user) {
                foreach ($user->qualifications as $qualification) {
                    if ($qualification->document_file) {
                        Storage::disk('s3')->delete($qualification->document_file);
                    }
                    $qualification->delete();
                }
            }

            $applicant->delete();

            return response()->json(['message' => 'Applicant deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete applicant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get applicant status including profile completion, first approval, and appointment
     */
    public function getApplicantStatus(Request $request)
    {
        $user = $request->user();

        // 0) Authz: must be applicant
        if (!$user || $user->role !== UserRole::APPLICANT) {
            return response()->json(['message' => 'Only applicants can view their status'], 403);
        }

        $applicant = $user->applicant;
        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        // 1) Profile completion
        $isCompleted = (bool) ($applicant->is_completed ?? false);

        $haveFirstApproval = false;
        $hasActiveApplication = false;
        $appointment = null;

        if ($isCompleted) {
            // 2) Latest status at USER level (eager-load is cheap here)
            $user->load('currentStatus');
            $latestStatus = $user->currentStatus; // may be null if no applications/statuses yet

            if ($latestStatus) {
                Log::info('Latest status found: ' . $latestStatus->status_name);

                // a) first_approval flag
                if (in_array($latestStatus->status_name, ['first_approval', 'meeting_scheduled'], true)) {
                    $haveFirstApproval = true;
                }

                // b) active application flag
                if (
                    in_array(
                        $latestStatus->status_name,
                        ['enrolled', 'first_approval', 'meeting_scheduled', 'second_approval', 'final_approval'],
                        true
                    )
                ) {
                    $hasActiveApplication = true;
                }
            } else {
                Log::info('No latest status found - applicant has no applications yet');
            }

            // 3) Appointment logic
            if ($haveFirstApproval || ($latestStatus && $latestStatus->status_name === 'meeting_scheduled')) {
                // Check for a *booked* future appointment
                $bookedAppointment = Appointment::where('user_id', $user->user_id)
                    ->where('status', 'booked')
                    ->where('starts_at_utc', '>', now())
                    ->first();

                if ($bookedAppointment) {
                    $appointment = $bookedAppointment; // format later
                } else {
                    // No booked slot; provide available upcoming slots
                    $availableAppointments = Appointment::where('status', 'available')
                        ->where('starts_at_utc', '>', now())
                        ->orderBy('starts_at_utc')
                        ->get();

                    if ($availableAppointments->count() > 0) {
                        $applicantTimezone = $user->timezone ?? 'UTC';

                        $appointment = [
                            'type' => 'available_appointments',
                            'count' => $availableAppointments->count(),
                            'appointments' => $availableAppointments->map(function ($apt) use ($applicantTimezone) {
                                $startsAtLocal = $apt->starts_at_utc->setTimezone($applicantTimezone);
                                $endsAtLocal = $apt->ends_at_utc->setTimezone($applicantTimezone);

                                return [
                                    'appointment_id' => $apt->appointment_id,
                                    'starts_at_utc' => $apt->starts_at_utc,
                                    'ends_at_utc' => $apt->ends_at_utc,
                                    'starts_at_local' => $startsAtLocal->format('Y-m-d H:i:s'),
                                    'ends_at_local' => $endsAtLocal->format('Y-m-d H:i:s'),
                                    'starts_at_display' => $startsAtLocal->format('M j, Y g:i A'),
                                    'ends_at_display' => $endsAtLocal->format('M j, Y g:i A'),
                                    'duration_min' => $apt->duration_min,
                                    'owner_timezone' => $apt->owner_timezone,
                                    'applicant_timezone' => $applicantTimezone,
                                    'meeting_url' => $apt->meeting_url,
                                    'status' => $apt->status,
                                ];
                            })->toArray(),
                        ];
                    }
                }
            }

            // 4) If we found a booked appointment, format it for response
            if ($appointment && !isset($appointment['type'])) {
                $applicantTimezone = $user->timezone ?? 'UTC';
                $startsAtLocal = $appointment->starts_at_utc->setTimezone($applicantTimezone);
                $endsAtLocal = $appointment->ends_at_utc->setTimezone($applicantTimezone);

                $appointment = [
                    'type' => 'booked_appointment',
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
                ];
            }
        }

        return response()->json([
            'is_completed' => $isCompleted,
            'has_active_application' => $hasActiveApplication,
            'have_first_approval' => $haveFirstApproval,
            'appointment' => $appointment,
        ]);
    }

}
