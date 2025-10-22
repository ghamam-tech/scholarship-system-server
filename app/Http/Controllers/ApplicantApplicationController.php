<?php

namespace App\Http\Controllers;

use App\Models\ApplicantApplication;
use App\Models\ApplicantApplicationStatus;
use App\Models\Qualification;
use App\Models\Applicant;
use App\Models\Scholarship;
use App\Models\Appointment;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApplicantApplicationController extends Controller
{


    public function store(Request $request)
    {
        $request->merge([
            'program_details' => array_merge($request->input('program_details', []), [
                'has_active_program' => filter_var($request->input('program_details.has_active_program'), FILTER_VALIDATE_BOOLEAN),
                'terms_and_condition' => filter_var($request->input('program_details.terms_and_condition'), FILTER_VALIDATE_BOOLEAN),
            ]),
        ]);

        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found. Please complete your profile first.'], 404);
        }

        if (!$applicant->is_completed) {
            return response()->json(['message' => 'Please complete your profile first before submitting an application'], 422);
        }

        if ($applicant->applications()->exists()) {
            return response()->json(['message' => 'You can only submit one application per scholarship cycle'], 422);
        }

        $data = $request->validate([
            'program_details.scholarship_id' => ['required', 'exists:scholarships,scholarship_id'],
            'program_details.specialization_1' => ['required', 'string', 'max:255'],
            'program_details.specialization_2' => ['nullable', 'string', 'max:255'],
            'program_details.specialization_3' => ['nullable', 'string', 'max:255'],
            'program_details.university_name' => ['required', 'string', 'max:255'],
            'program_details.country_name' => ['required', 'string', 'max:100'],
            'program_details.tuition_fee' => ['nullable', 'numeric', 'min:0'],
            'program_details.has_active_program' => ['required', 'boolean'],
            'program_details.current_semester_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'program_details.cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'program_details.cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
            'program_details.terms_and_condition' => ['required', 'accepted'],
            'program_details.offer_letter' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            DB::beginTransaction();

            $scholarship = Scholarship::where('scholarship_id', $data['program_details']['scholarship_id'])
                ->where('is_active', true)
                ->where('opening_date', '<=', now())
                ->where('closing_date', '>=', now())
                ->first();

            if (!$scholarship) {
                return response()->json(['message' => 'Selected scholarship is not available'], 422);
            }

            $application = ApplicantApplication::create([
                'applicant_id' => $applicant->applicant_id,
                'scholarship_id' => $data['program_details']['scholarship_id'],
                'specialization_1' => $data['program_details']['specialization_1'],
                'specialization_2' => $data['program_details']['specialization_2'] ?? null,
                'specialization_3' => $data['program_details']['specialization_3'] ?? null,
                'university_name' => $data['program_details']['university_name'],
                'country_name' => $data['program_details']['country_name'],
                'tuition_fee' => $data['program_details']['tuition_fee'] ?? null,
                'has_active_program' => $data['program_details']['has_active_program'],
                'current_semester_number' => $data['program_details']['current_semester_number'] ?? null,
                'cgpa' => $data['program_details']['cgpa'] ?? null,
                'cgpa_out_of' => $data['program_details']['cgpa_out_of'] ?? null,
                'terms_and_condition' => $data['program_details']['terms_and_condition'],
            ]);

            if ($request->hasFile('program_details.offer_letter')) {
                $file = $request->file('program_details.offer_letter');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

                $path = $file->storeAs(
                    "applications/{$applicant->applicant_id}/{$application->application_id}/offer-letter",
                    $filename,
                    's3'
                );

                $application->update([
                    'offer_letter_file' => config('filesystems.disks.s3.url') . '/' . $path
                ]);
            }

            ApplicantApplicationStatus::create([
                'user_id' => $request->user()->user_id,
                'status_name' => ApplicationStatus::ENROLLED->value,
                'date' => now(),
                'comment' => 'Application submitted',
            ]);

            DB::commit();

            // Eager-load nested current status (user-level)
            $application->load([
                'scholarship',
                'applicant.user.currentStatus',
            ]);

            $cs = optional(optional($application->applicant)->user)->currentStatus;

            return response()->json([
                'message' => 'Application submitted successfully',
                'application' => array_merge($application->toArray(), [
                    'current_status' => $cs, // includes status_name, date, comment...
                ]),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit application',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update program details for an existing application
     */
    public function updateProgramDetails(Request $request, $applicationId)
    {
        $application = ApplicantApplication::findOrFail($applicationId);

        if ($application->applicant_id !== $request->user()->applicant->applicant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'specialization_1' => ['required', 'string', 'max:255'],
            'specialization_2' => ['nullable', 'string', 'max:255'],
            'specialization_3' => ['nullable', 'string', 'max:255'],
            'university_name' => ['required', 'string', 'max:255'],
            'country_name' => ['required', 'string', 'max:100'],
            'tuition_fee' => ['nullable', 'numeric', 'min:0'],
            'has_active_program' => ['required', 'boolean'],
            'current_semester_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
            'terms_and_condition' => ['required', 'accepted'],
            'offer_letter_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            $offerLetterFile = $application->offer_letter_file;
            if ($request->hasFile('offer_letter_file')) {
                if ($offerLetterFile) {
                    Storage::disk('s3')->delete($offerLetterFile);
                }
                $filename = time() . '_' . str_replace(' ', '_', $request->file('offer_letter_file')->getClientOriginalName());
                $offerLetterPath = $request->file('offer_letter_file')->storeAs("application-documents/offer-letters/", $filename, 's3');
                $offerLetterFile = config('filesystems.disks.s3.url') . '/' . $offerLetterPath;
            }

            $application->update([
                'specialization_1' => $data['specialization_1'],
                'specialization_2' => $data['specialization_2'] ?? null,
                'specialization_3' => $data['specialization_3'] ?? null,
                'university_name' => $data['university_name'],
                'country_name' => $data['country_name'],
                'tuition_fee' => $data['tuition_fee'] ?? null,
                'has_active_program' => $data['has_active_program'],
                'current_semester_number' => $data['current_semester_number'] ?? null,
                'cgpa' => $data['cgpa'] ?? null,
                'cgpa_out_of' => $data['cgpa_out_of'] ?? null,
                'terms_and_condition' => $data['terms_and_condition'],
                'offer_letter_file' => $offerLetterFile,
            ]);

            return response()->json([
                'message' => 'Program details updated successfully',
                'application' => $application
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update program details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update application status (Admin only)
     */
    public function addStatus(Request $request, $applicationId)
    {
        $application = ApplicantApplication::findOrFail($applicationId);
        $userId = $application->applicant->user_id;

        $data = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Block reject if already final_approval
        $latest = ApplicantApplicationStatus::where('user_id', $userId)
            ->orderBy('date', 'desc')->orderBy('created_at', 'desc')->first();

        if (
            $data['status'] === ApplicationStatus::REJECTED->value
            && $latest && $latest->status_name === ApplicationStatus::FINAL_APPROVAL->value
        ) {
            return response()->json(['message' => 'Cannot reject application after final approval'], 422);
        }

        try {
            DB::beginTransaction();

            ApplicantApplicationStatus::create([
                'user_id' => $userId,
                'status_name' => $data['status'],
                'date' => now(),
                'comment' => $data['comment'] ?? null,
            ]);

            DB::commit();

            // if you still want "current_status", compute from user-level statuses:
            $currentStatus = ApplicantApplicationStatus::where('user_id', $userId)
                ->orderBy('date', 'desc')->orderBy('created_at', 'desc')->first();

            return response()->json([
                'message' => 'Application status updated successfully',
                'current_status' => $currentStatus,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update application status', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Get application with full details
     */
    public function show(Request $request, $applicationId)
    {
        $application = ApplicantApplication::with([
            'applicant.user.currentStatus',   // latest
            'applicant.user',                 // needed to read user_id safely
            'applicant.qualifications',
            'scholarship',
        ])->findOrFail($applicationId);

        if (
            $request->user()->role->value !== 'admin' &&
            $application->applicant_id !== $request->user()->applicant->applicant_id
        ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $userId = optional(optional($application->applicant)->user)->user_id;

        // If you want full trail on this screen:
        $statusTrail = $userId
            ? ApplicantApplicationStatus::where('user_id', $userId)
                ->orderBy('date', 'desc')->orderBy('created_at', 'desc')
                ->get()
            : collect();

        $cs = optional(optional($application->applicant)->user)->currentStatus;

        return response()->json([
            'application' => $application,
            'status_trail' => $statusTrail,
            'current_status' => $cs,
        ]);
    }

    /**
     * Get all applications for authenticated applicant
     */
    public function index(Request $request)
    {
        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $applications = ApplicantApplication::with([
            'scholarship',
            'applicant.qualifications',
            'applicant.user.currentStatus',
        ])
            ->where('applicant_id', $applicant->applicant_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                $cs = optional(optional($application->applicant)->user)->currentStatus;

                return array_merge($application->toArray(), [
                    'current_status' => $cs
                        ? [
                            'status_name' => $cs->status_name,
                            'date' => $cs->date,
                            'comment' => $cs->comment,
                        ]
                        : null,
                ]);
            });

        return response()->json($applications);
    }

    /**
     * Delete application (Admin only)
     */
    public function destroy(Request $request, $applicationId)
    {
        if ($request->user()->role->value !== 'admin') {
            return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
        }

        $application = ApplicantApplication::findOrFail($applicationId);

        try {
            DB::beginTransaction();

            if ($application->offer_letter_file) {
                // ensure this column stores a PATH (not full URL)
                Storage::disk('s3')->delete($application->offer_letter_file);
            }

            $application->delete();

            DB::commit();

            return response()->json(['message' => 'Application deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get application statistics (Admin only)
     */
    public function getStatistics(Request $request)
    {
        if ($request->user()->role->value !== 'admin') {
            return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
        }

        $total = ApplicantApplication::count();

        $countBy = function ($status) {
            return ApplicantApplication::whereHas('applicant.user.currentStatus', function ($q) use ($status) {
                $q->where('status_name', $status);
            })->count();
        };

        $stats = [
            'total_applications' => $total,
            'enrolled' => $countBy(ApplicationStatus::ENROLLED->value),
            'first_approval' => $countBy(ApplicationStatus::FIRST_APPROVAL->value),
            'second_approval' => $countBy(ApplicationStatus::SECOND_APPROVAL->value),
            'final_approval' => $countBy(ApplicationStatus::FINAL_APPROVAL->value),
            'rejected' => $countBy(ApplicationStatus::REJECTED->value),
        ];

        return response()->json($stats);
    }

    /**
     * Handle document uploads for applicant
     */
    private function handleDocumentUploads(Request $request, Applicant $applicant)
    {
        $documentFields = [
            'passport_copy' => ['passport_copy_img', 'applicant-documents/passport/'],
            'personal_image' => ['personal_image', 'applicant-documents/personal-images/'],
            'secondary_school_certificate' => ['tahsili_file', 'applicant-documents/tahsili/'],
            'secondary_school_transcript' => ['qudorat_file', 'applicant-documents/qudorat/'],
            'volunteering_certificate' => ['volunteering_certificate_file', 'applicant-documents/volunteering/'],
        ];

        foreach ($documentFields as $requestField => $config) {
            $dbField = $config[0];
            $s3Folder = $config[1];

            if ($request->hasFile($requestField)) {
                if ($applicant->$dbField) {
                    Storage::disk('s3')->delete($applicant->$dbField);
                }

                $filename = time() . '_' . str_replace(' ', '_', $request->file($requestField)->getClientOriginalName());
                $filePath = $request->file($requestField)->storeAs($s3Folder, $filename, 's3');
                $fullUrl = config('filesystems.disks.s3.url') . '/' . $filePath;
                $applicant->update([$dbField => $fullUrl]);
            }
        }
    }

    /**
     * Validate scholarships
     */
    private function validateScholarships(array $scholarshipIds)
    {
        $scholarships = Scholarship::whereIn('scholarship_id', $scholarshipIds)
            ->where('is_active', true)
            ->where('opening_date', '<=', now())
            ->where('closing_date', '>=', now())
            ->get();

        if (count($scholarships) !== count($scholarshipIds)) {
            throw new \Exception('One or more scholarships are not available');
        }

        return $scholarshipIds;
    }

    /**
     * Get the latest status for an application
     */
    private function getLatestStatus($application)
    {
        $userId = $application->applicant->user_id;

        return ApplicantApplicationStatus::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get all enrolled applications only
     */
    public function submittedApplications(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view submitted applications'], 403);
        }

        $applications = ApplicantApplication::with([
            'applicant.user.currentStatus',
            'scholarship',
            'scholarship.countries',
        ])
            ->whereHas('applicant.user.currentStatus', function ($q) {
                $q->where('status_name', ApplicationStatus::ENROLLED->value);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                $applicant = $application->applicant;
                $scholarship = $application->scholarship;
                $countries = $scholarship ? $scholarship->countries : collect();
                $firstCountry = $countries->first();

                $cgpa = $application->cgpa;
                $cgpaOutOf = $application->cgpa_out_of;
                $cgpaFormatted = $cgpa && $cgpaOutOf ? "{$cgpa}/{$cgpaOutOf}" : null;

                $cs = optional(optional($applicant)->user)->currentStatus;

                return [
                    'application_id' => $application->application_id,
                    'applicant_id' => $applicant ? $applicant->applicant_id : null,
                    'applicant_name' => $applicant ? $applicant->ar_name : 'N/A',
                    'scholarship_id' => $scholarship ? $scholarship->scholarship_id : null,
                    'scholarship_name' => $scholarship ? $scholarship->scholarship_name : 'N/A',
                    'nationality' => $applicant ? $applicant->nationality : 'N/A',
                    'country_id' => $firstCountry ? $firstCountry->country_id : null,
                    'country_name' => $firstCountry ? $firstCountry->country_name : 'N/A',
                    'cgpa' => $cgpaFormatted,
                    'program_type' => $scholarship ? ($scholarship->scholarship_type ?? 'N/A') : 'N/A',
                    'current_status' => $cs->status_name ?? 'N/A',
                    'status_date' => $cs->date ?? null,
                    'status_comment' => $cs->comment ?? null,
                ];
            });

        $statusCounts = $applications->groupBy('current_status')->map->count();

        return response()->json([
            'data' => $applications,
            'meta' => [
                'total' => $applications->count(),
                'status_counts' => [
                    'submitted' => $statusCounts->get('submitted', 0),
                    'first_approval' => $statusCounts->get('first_approval', 0),
                    'meeting_scheduled' => $statusCounts->get('meeting_scheduled', 0),
                    'second_approval' => $statusCounts->get('second_approval', 0),
                    'final_approval' => $statusCounts->get('final_approval', 0),
                    'enrolled' => $statusCounts->get('enrolled', 0),
                    'rejected' => $statusCounts->get('rejected', 0),
                    'other' => $statusCounts->get('N/A', 0),
                ],
                'enrolled_count' => $statusCounts->get('enrolled', 0),
                'rejected_count' => $statusCounts->get('rejected', 0),
            ]
        ]);
    }


    /**
     * Get all applications with same output format as submittedApplications (admin only)
     */
    public function getAllApplications(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view all applications'], 403);
        }

        $applications = ApplicantApplication::with([
            'applicant.user.currentStatus',
            'scholarship',
            'scholarship.countries',
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                $applicant = $application->applicant;
                $scholarship = $application->scholarship;
                $countries = $scholarship ? $scholarship->countries : collect();
                $firstCountry = $countries->first();

                $cgpa = $application->cgpa;
                $cgpaOutOf = $application->cgpa_out_of;
                $cgpaFormatted = $cgpa && $cgpaOutOf ? "{$cgpa}/{$cgpaOutOf}" : null;

                $cs = optional(optional($applicant)->user)->currentStatus;

                return [
                    'application_id' => $application->application_id,
                    'applicant_id' => $applicant ? $applicant->applicant_id : null,
                    'applicant_name' => $applicant ? $applicant->ar_name : 'N/A',
                    'scholarship_id' => $scholarship ? $scholarship->scholarship_id : null,
                    'scholarship_name' => $scholarship ? $scholarship->scholarship_name : 'N/A',
                    'nationality' => $applicant ? $applicant->nationality : 'N/A',
                    'country_id' => $firstCountry ? $firstCountry->country_id : null,
                    'country_name' => $firstCountry ? $firstCountry->country_name : 'N/A',
                    'cgpa' => $cgpaFormatted,
                    'program_type' => $scholarship ? ($scholarship->scholarship_type ?? 'N/A') : 'N/A',
                    'current_status' => $cs->status_name ?? 'N/A',
                    'status_date' => $cs->date ?? null,
                    'status_comment' => $cs->comment ?? null,
                ];
            });

        $statusCounts = $applications->groupBy('current_status')->map->count();

        return response()->json([
            'data' => $applications,
            'meta' => [
                'total' => $applications->count(),
                'status_counts' => [
                    'submitted' => $statusCounts->get('submitted', 0),
                    'first_approval' => $statusCounts->get('first_approval', 0),
                    'meeting_scheduled' => $statusCounts->get('meeting_scheduled', 0),
                    'second_approval' => $statusCounts->get('second_approval', 0),
                    'final_approval' => $statusCounts->get('final_approval', 0),
                    'enrolled' => $statusCounts->get('enrolled', 0),
                    'rejected' => $statusCounts->get('rejected', 0),
                    'other' => $statusCounts->get('N/A', 0),
                ],
                'enrolled_count' => $statusCounts->get('enrolled', 0),
                'rejected_count' => $statusCounts->get('rejected', 0),
            ]
        ]);
    }

    /**
     * Get all applications with their current status (admin only)
     */
    public function getAllApplicationsWithStatus(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view all applications'], 403);
        }

        $applications = ApplicantApplication::with([
            'applicant.user.currentStatus',
            'scholarship',
            'scholarship.countries',
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                $applicant = $application->applicant;
                $scholarship = $application->scholarship;
                $countries = $scholarship ? $scholarship->countries : collect();
                $firstCountry = $countries->first();

                $cgpa = $application->cgpa;
                $cgpaOutOf = $application->cgpa_out_of;
                $cgpaFormatted = $cgpa && $cgpaOutOf ? "{$cgpa}/{$cgpaOutOf}" : null;

                $cs = optional(optional($applicant)->user)->currentStatus;

                return [
                    'application_id' => $application->application_id,
                    'applicant_id' => $applicant ? $applicant->applicant_id : null,
                    'applicant_name' => $applicant ? $applicant->ar_name : 'N/A',
                    'scholarship_id' => $scholarship ? $scholarship->scholarship_id : null,
                    'scholarship_name' => $scholarship ? $scholarship->scholarship_name : 'N/A',
                    'nationality' => $applicant ? $applicant->nationality : 'N/A',
                    'country_id' => $firstCountry ? $firstCountry->country_id : null,
                    'country_name' => $firstCountry ? $firstCountry->country_name : 'N/A',
                    'cgpa' => $cgpaFormatted,
                    'program_type' => $scholarship ? ($scholarship->scholarship_type ?? 'N/A') : 'N/A',
                    'current_status' => $cs->status_name ?? 'N/A',
                    'status_date' => $cs->date ?? null,
                    'status_comment' => $cs->comment ?? null,
                ];
            });

        $statusCounts = $applications->groupBy('current_status')->map->count();

        return response()->json([
            'data' => $applications,
            'meta' => [
                'total' => $applications->count(),
                'status_counts' => [
                    'submitted' => $statusCounts->get('submitted', 0),
                    'first_approval' => $statusCounts->get('first_approval', 0),
                    'meeting_scheduled' => $statusCounts->get('meeting_scheduled', 0),
                    'second_approval' => $statusCounts->get('second_approval', 0),
                    'final_approval' => $statusCounts->get('final_approval', 0),
                    'enrolled' => $statusCounts->get('enrolled', 0),
                    'rejected' => $statusCounts->get('rejected', 0),
                    'other' => $statusCounts->get('N/A', 0),
                ],
            ]
        ]);
    }

    /**
     * Get applications with first_approval and meeting_scheduled status
     */
    public function firstApproval(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view first approval applications'], 403);
        }

        $applications = ApplicantApplication::with([
            'applicant.user.currentStatus',
            'scholarship',
        ])
            ->whereHas('applicant.user.currentStatus', function ($q) {
                $q->whereIn('status_name', [
                    ApplicationStatus::FIRST_APPROVAL->value,
                    ApplicationStatus::MEETING_SCHEDULED->value,
                ]);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) use ($user) {
                $applicant = $application->applicant;
                $scholarship = $application->scholarship;
                $applicantUser = $applicant ? $applicant->user : null;

                $cgpa = $application->cgpa;
                $cgpaOutOf = $application->cgpa_out_of;
                $cgpaFormatted = $cgpa && $cgpaOutOf ? "{$cgpa}/{$cgpaOutOf}" : null;

                $cs = optional($applicantUser)->currentStatus;

                // Meeting status: only when current status is "meeting_scheduled"
                $meetingStatus = 'not set';
                if ($cs && $cs->status_name === ApplicationStatus::MEETING_SCHEDULED->value && $applicantUser) {
                    $appointment = Appointment::where('user_id', $applicantUser->user_id)
                        ->where('status', 'booked')
                        ->where('starts_at_utc', '>', now())
                        ->first();

                    if ($appointment) {
                        $adminTimezone = $user->timezone ?? 'UTC';
                        $startsAtLocal = $appointment->starts_at_utc->setTimezone($adminTimezone);
                        $meetingStatus = $startsAtLocal->format('Y-m-d g:i A');
                    }
                }

                return [
                    'application_id' => $application->application_id,
                    'applicant_id' => $applicant ? $applicant->applicant_id : null,
                    'applicant_name' => $applicant ? $applicant->ar_name : 'N/A',
                    'scholarship_id' => $scholarship ? $scholarship->scholarship_id : null,
                    'scholarship_name' => $scholarship ? $scholarship->scholarship_name : 'N/A',
                    'cgpa' => $cgpaFormatted,
                    'program_type' => $scholarship ? ($scholarship->scholarship_type ?? 'N/A') : 'N/A',
                    'current_status' => $cs->status_name ?? 'N/A',
                    'status_date' => $cs->date ?? null,
                    'status_comment' => $cs->comment ?? null,
                    'meeting_status' => $meetingStatus,
                ];
            });

        $statusCounts = $applications->groupBy('current_status')->map->count();

        return response()->json([
            'data' => $applications,
            'meta' => [
                'total' => $applications->count(),
                'first_approval_count' => $statusCounts->get('first_approval', 0),
                'meeting_scheduled_count' => $statusCounts->get('meeting_scheduled', 0),
            ]
        ]);
    }

    /**
     * Get applicant status including profile completion, first approval, and appointment
     */
    public function getApplicantStatus(Request $request)
    {
        $user = $request->user();

        // Check if user is an applicant
        if (!$user || $user->role !== UserRole::APPLICANT) {
            return response()->json(['message' => 'Only applicants can view their status'], 403);
        }

        $applicant = $user->applicant;
        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        // Debug: Log user and applicant info
        \Illuminate\Support\Facades\Log::info('User ID: ' . $user->user_id . ', Applicant ID: ' . ($applicant ? $applicant->applicant_id : 'null'));

        // 1. Check if profile is completed
        $isCompleted = $applicant->is_completed ?? false;

        // 2. Check if applicant has active application
        $hasActiveApplication = false;
        if ($isCompleted) {
            // Get the latest status for any application
            $latestStatus = $applicant->applications()
                ->with([
                    'statuses' => function ($query) {
                        $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
                    }
                ])
                ->get()
                ->flatMap(function ($application) {
                    return $application->statuses;
                })
                ->sortByDesc('date')
                ->sortByDesc('created_at')
                ->first();

            // Check if the latest status is active (only first_approval is considered active)
            if ($latestStatus && $latestStatus->status_name === 'first_approval') {
                $hasActiveApplication = true;
            }
        }

        // 3. Check if applicant has first approval (reuse the latestStatus from above)
        $haveFirstApproval = false;
        $appointment = null;

        if ($isCompleted && $latestStatus) {
            // Debug: Check what status we actually have
            \Illuminate\Support\Facades\Log::info('Latest status found: ' . $latestStatus->status_name);

            if (in_array($latestStatus->status_name, ['first_approval', 'meeting_scheduled'])) {
                $haveFirstApproval = true;
            }

            // 4. Check appointments based on status
            $appointment = null;

            if ($haveFirstApproval) {
                // Check for booked appointment first
                $bookedAppointment = Appointment::where('user_id', $user->user_id)
                    ->where('status', 'booked')
                    ->where('starts_at_utc', '>', now())
                    ->first();

                if ($bookedAppointment) {
                    // User has a booked appointment
                    $appointment = $bookedAppointment;
                } else {
                    // User has first approval but no booked appointment, return available appointments
                    $availableAppointments = Appointment::where('status', 'available')
                        ->where('starts_at_utc', '>', now())
                        ->orderBy('starts_at_utc')
                        ->get();

                    if ($availableAppointments->count() > 0) {
                        // Get applicant's timezone for display
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
                            })->toArray()
                        ];
                    }
                }
            }

            // Debug: Check appointment search
            \Illuminate\Support\Facades\Log::info('Looking for appointment for user_id: ' . $user->user_id);
            \Illuminate\Support\Facades\Log::info('Found appointment: ' . ($appointment ? 'Yes' : 'No'));

            // Format booked appointment if found
            if ($appointment && !isset($appointment['type'])) {
                // Get applicant's timezone for display
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
    /**
     * Get applications with second_approval status
     */
    public function secondApproval(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view second approval applications'], 403);
        }

        $applications = ApplicantApplication::with([
            'applicant.user.currentStatus',
            'scholarship',
        ])
            ->whereHas('applicant.user.currentStatus', function ($q) {
                $q->where('status_name', ApplicationStatus::SECOND_APPROVAL->value);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($application) {
                $applicant = $application->applicant;
                $scholarship = $application->scholarship;
                $userId = optional($applicant->user)->user_id;

                $cs = optional(optional($applicant)->user)->currentStatus;

                // previous first_approval comment
                $previousApprovalComment = null;
                if ($userId) {
                    $firstApproval = ApplicantApplicationStatus::where('user_id', $userId)
                        ->where('status_name', ApplicationStatus::FIRST_APPROVAL->value)
                        ->orderBy('date', 'desc')->orderBy('created_at', 'desc')
                        ->first();

                    $previousApprovalComment = optional($firstApproval)->comment;
                }

                return [
                    'application_id' => $application->application_id,
                    'applicant_id' => $applicant ? $applicant->applicant_id : null,
                    'applicant_name' => $applicant ? $applicant->ar_name : 'N/A',
                    'scholarship_id' => $scholarship ? $scholarship->scholarship_id : null,
                    'scholarship_name' => $scholarship ? $scholarship->scholarship_name : 'N/A',
                    'current_status' => $cs->status_name ?? 'N/A',
                    'status_date' => $cs->date ?? null,
                    'status_comment' => $cs->comment ?? null,
                    'comment_from_previous_approval' => $previousApprovalComment,
                ];
            });

        return response()->json([
            'data' => $applications,
            'meta' => [
                'total' => $applications->count(),
            ]
        ]);
    }

    /**
     * Get application by id with full details: applicant, qualifications, scholarship,
     * full status trail, current status, and appointment info
     */
    public function getApplicationById(Request $request, $applicationId)
    {
        $application = ApplicantApplication::with([
            'applicant.user.currentStatus',
            'applicant.user',
            'applicant.qualifications',
            'scholarship',
            'scholarship.countries',
        ])->findOrFail($applicationId);

        $user = $request->user();
        if (!$user || $user->role->value !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cs = optional(optional($application->applicant)->user)->currentStatus;
        $hasFirstApproval = $cs && in_array($cs->status_name, [
            ApplicationStatus::FIRST_APPROVAL->value,
            ApplicationStatus::MEETING_SCHEDULED->value,
        ]);

        $appointmentInfo = null;
        $applicantUser = optional($application->applicant)->user;

        if ($hasFirstApproval && $applicantUser) {
            $booked = Appointment::where('user_id', $applicantUser->user_id)
                ->where('status', 'booked')
                ->where('starts_at_utc', '>', now())
                ->first();

            if ($booked) {
                $viewerTz = $user->timezone ?? 'UTC';
                $startsAtLocal = $booked->starts_at_utc->setTimezone($viewerTz);
                $endsAtLocal = $booked->ends_at_utc->setTimezone($viewerTz);

                $appointmentInfo = [
                    'type' => 'booked_appointment',
                    'appointment_id' => $booked->appointment_id,
                    'starts_at_utc' => $booked->starts_at_utc,
                    'ends_at_utc' => $booked->ends_at_utc,
                    'starts_at_local' => $startsAtLocal->format('Y-m-d H:i:s'),
                    'ends_at_local' => $endsAtLocal->format('Y-m-d H:i:s'),
                    'starts_at_display' => $startsAtLocal->format('M j, Y g:i A'),
                    'ends_at_display' => $endsAtLocal->format('M j, Y g:i A'),
                    'duration_min' => $booked->duration_min,
                    'owner_timezone' => $booked->owner_timezone,
                    'viewer_timezone' => $viewerTz,
                    'meeting_url' => $booked->meeting_url,
                    'status' => $booked->status,
                    'booked_at' => $booked->booked_at,
                ];
            } else {
                $available = Appointment::where('status', 'available')
                    ->where('starts_at_utc', '>', now())
                    ->orderBy('starts_at_utc')
                    ->get();

                if ($available->count() > 0) {
                    $viewerTz = $user->timezone ?? 'UTC';
                    $appointmentInfo = [
                        'type' => 'available_appointments',
                        'count' => $available->count(),
                        'appointments' => $available->map(function ($apt) use ($viewerTz) {
                            $startsAtLocal = $apt->starts_at_utc->setTimezone($viewerTz);
                            $endsAtLocal = $apt->ends_at_utc->setTimezone($viewerTz);
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
                                'viewer_timezone' => $viewerTz,
                                'meeting_url' => $apt->meeting_url,
                                'status' => $apt->status,
                            ];
                        })->toArray(),
                    ];
                }
            }
        }

        // Full status trail (if you want to include it here)
        $userId = optional(optional($application->applicant)->user)->user_id;
        $statusTrail = $userId
            ? ApplicantApplicationStatus::where('user_id', $userId)
                ->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get()
            : collect();

        return response()->json([
            'application' => [
                'application_id' => $application->application_id,
                'applicant' => $application->applicant,
                'qualifications' => optional($application->applicant)->qualifications ?? [],
                'scholarship' => $application->scholarship,
                'status_trail' => $statusTrail,
                'current_status' => $cs,
                'cgpa' => $application->cgpa,
                'cgpa_out_of' => $application->cgpa_out_of,
                'country_name' => $application->country_name,
                'university_name' => $application->university_name,
                'specialization_1' => $application->specialization_1,
                'specialization_2' => $application->specialization_2,
                'specialization_3' => $application->specialization_3,
                'offer_letter_file' => $application->offer_letter_file,
            ],
            'appointment' => $appointmentInfo,
        ]);
    }
}
