<?php

namespace App\Http\Controllers;

use App\Models\ApplicantApplication;
use App\Models\ApplicantApplicationStatus;
use App\Models\Qualification;
use App\Models\Applicant;
use App\Models\Scholarship;
use App\Enums\ApplicationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApplicantApplicationController extends Controller
{
    /**
     * Submit complete application in one request
     */
    // public function submitCompleteApplication(Request $request)
    // {
    //     $applicant = $request->user()->applicant;

    //     if (!$applicant) {
    //         return response()->json(['message' => 'Applicant profile not found'], 404);
    //     }

    //     // Check if applicant profile is completed
    //     if (!$applicant->is_completed) {
    //         return response()->json(['message' => 'Please complete your profile first before submitting an application'], 422);
    //     }

    //     // Check if applicant already has an application
    //     if ($applicant->applications()->exists()) {
    //         return response()->json(['message' => 'You can only submit one application per scholarship cycle'], 422);
    //     }

    //     // Validate all data at once
    //     $data = $request->validate([
    //         // Personal Info
    //         'personal_info.ar_name' => ['required', 'string', 'max:255'],
    //         'personal_info.en_name' => ['required', 'string', 'max:255'],
    //         'personal_info.nationality' => ['required', 'string', 'max:100'],
    //         'personal_info.gender' => ['required', 'string', 'in:male,female'],
    //         'personal_info.place_of_birth' => ['required', 'string', 'max:255'],
    //         'personal_info.phone' => ['required', 'string', 'max:20'],
    //         'personal_info.passport_number' => ['required', 'string', 'max:50'],
    //         'personal_info.date_of_birth' => ['required', 'date'],
    //         'personal_info.parent_contact_name' => ['required', 'string', 'max:255'],
    //         'personal_info.parent_contact_phone' => ['required', 'string', 'max:20'],
    //         'personal_info.residence_country' => ['required', 'string', 'max:100'],
    //         'personal_info.language' => ['required', 'string', 'max:50'],
    //         'personal_info.is_studied_in_saudi' => ['required', 'boolean'],
    //         'personal_info.tahseeli_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
    //         'personal_info.qudorat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

    //         // Academic Info
    //         'academic_info.qualifications' => ['required', 'array', 'min:1'],
    //         'academic_info.qualifications.*.qualification_type' => ['required', Rule::in(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other'])],
    //         'academic_info.qualifications.*.institute_name' => ['required', 'string', 'max:255'],
    //         'academic_info.qualifications.*.year_of_graduation' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
    //         'academic_info.qualifications.*.cgpa' => ['nullable', 'numeric', 'min:0'],
    //         'academic_info.qualifications.*.cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
    //         'academic_info.qualifications.*.language_of_study' => ['nullable', 'string', 'max:100'],
    //         'academic_info.qualifications.*.specialization' => ['nullable', 'string', 'max:255'],
    //         'academic_info.qualifications.*.research_title' => ['nullable', 'string', 'max:500'],
    //         'academic_info.qualifications.*.document_file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],

    //         // Program Details
    //         'program_details.scholarship_id' => ['required', 'exists:scholarships,scholarship_id'],
    //         'program_details.specialization_1' => ['required', 'string', 'max:255'],
    //         'program_details.specialization_2' => ['nullable', 'string', 'max:255'],
    //         'program_details.specialization_3' => ['nullable', 'string', 'max:255'],
    //         'program_details.university_name' => ['required', 'string', 'max:255'],
    //         'program_details.country_name' => ['required', 'string', 'max:100'],
    //         'program_details.tuition_fee' => ['nullable', 'numeric', 'min:0'],
    //         'program_details.has_active_program' => ['required', 'boolean'],
    //         'program_details.current_semester_number' => ['nullable', 'integer', 'min:1', 'max:20'],
    //         'program_details.cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
    //         'program_details.cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
    //         'program_details.terms_and_condition' => ['required', 'accepted'],

    //         // Document files
    //         'passport_copy' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
    //         'personal_image' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
    //         'secondary_school_certificate' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
    //         'secondary_school_transcript' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
    //         'volunteering_certificate' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
    //         'offer_letter' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         // Step 1: Update Applicant Personal Info and mark as completed
    //         $applicant->update(array_merge($data['personal_info'], ['is_completed' => true]));

    //         // Step 2: Handle document uploads for applicant
    //         $this->handleDocumentUploads($request, $applicant);

    //         // Step 3: Validate Scholarship
    //         $scholarship = Scholarship::where('scholarship_id', $data['program_details']['scholarship_id'])
    //             ->where('is_active', true)
    //             ->where('opening_date', '<=', now())
    //             ->where('closing_date', '>=', now())
    //             ->first();

    //         if (!$scholarship) {
    //             return response()->json(['message' => 'Selected scholarship is not available'], 422);
    //         }

    //         // Step 4: Create Application
    //         $application = ApplicantApplication::create([
    //             'applicant_id' => $applicant->applicant_id,
    //             'scholarship_id' => $data['program_details']['scholarship_id'],
    //             'specialization_1' => $data['program_details']['specialization_1'],
    //             'specialization_2' => $data['program_details']['specialization_2'] ?? null,
    //             'specialization_3' => $data['program_details']['specialization_3'] ?? null,
    //             'university_name' => $data['program_details']['university_name'],
    //             'country_name' => $data['program_details']['country_name'],
    //             'tuition_fee' => $data['program_details']['tuition_fee'] ?? null,
    //             'has_active_program' => $data['program_details']['has_active_program'],
    //             'current_semester_number' => $data['program_details']['current_semester_number'] ?? null,
    //             'cgpa' => $data['program_details']['cgpa'] ?? null,
    //             'cgpa_out_of' => $data['program_details']['cgpa_out_of'] ?? null,
    //             'terms_and_condition' => $data['program_details']['terms_and_condition'],
    //         ]);

    //         // Step 5: Handle offer letter upload
    //         if ($request->hasFile('offer_letter')) {
    //             $filename = time() . '_' . str_replace(' ', '_', $request->file('offer_letter')->getClientOriginalName());
    //             $offerLetterPath = $request->file('offer_letter')->storeAs("application-documents/offer-letters/", $filename, 's3');
    //             $fullUrl = config('filesystems.disks.s3.url') . '/' . $offerLetterPath;
    //             $application->update(['offer_letter_file' => $fullUrl]);
    //         }

    //         // Step 6: Add Qualifications with document files
    //         foreach ($data['academic_info']['qualifications'] as $index => $qualData) {
    //             $documentFile = null;

    //             if ($request->hasFile("academic_info.qualifications.{$index}.document_file")) {
    //                 $filename = time() . '_' . str_replace(' ', '_', $request->file("academic_info.qualifications.{$index}.document_file")->getClientOriginalName());
    //                 $documentPath = $request->file("academic_info.qualifications.{$index}.document_file")
    //                     ->storeAs("application-documents/qualifications/", $filename, 's3');
    //                 $documentFile = config('filesystems.disks.s3.url') . '/' . $documentPath;
    //             }

    //             Qualification::create([
    //                 'applicant_id' => $applicant->applicant_id,
    //                 'qualification_type' => $qualData['qualification_type'],
    //                 'institute_name' => $qualData['institute_name'],
    //                 'year_of_graduation' => $qualData['year_of_graduation'],
    //                 'cgpa' => $qualData['cgpa'] ?? null,
    //                 'cgpa_out_of' => $qualData['cgpa_out_of'] ?? null,
    //                 'language_of_study' => $qualData['language_of_study'] ?? null,
    //                 'specialization' => $qualData['specialization'] ?? null,
    //                 'research_title' => $qualData['research_title'] ?? null,
    //                 'document_file' => $documentFile
    //             ]);
    //         }

    //         // Step 7: Set Initial Status
    //         ApplicantApplicationStatus::create([
    //             'application_id' => $application->application_id,
    //             'status_name' => ApplicationStatus::ENROLLED->value,
    //             'date' => now(),
    //             'comment' => 'Complete application submitted'
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Application submitted successfully',
    //             'application_id' => $application->application_id,
    //             'application' => $application->load(['currentStatus', 'scholarship', 'applicant.qualifications'])
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Failed to submit application',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        // At the start of store() in ApplicantApplicationController
        $request->merge([
            'program_details' => array_merge($request->input('program_details', []), [
                'has_active_program'   => filter_var($request->input('program_details.has_active_program'), FILTER_VALIDATE_BOOLEAN),
                'terms_and_condition'  => filter_var($request->input('program_details.terms_and_condition'), FILTER_VALIDATE_BOOLEAN),
            ]),
        ]);

        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found. Please complete your profile first.'], 404);
        }

        // Must have completed profile first
        if (!$applicant->is_completed) {
            return response()->json(['message' => 'Please complete your profile first before submitting an application'], 422);
        }

        // Only one application per cycle (keep your rule here)
        if ($applicant->applications()->exists()) {
            return response()->json(['message' => 'You can only submit one application per scholarship cycle'], 422);
        }

        $data = $request->validate([
            'program_details.scholarship_id'         => ['required', 'exists:scholarships,scholarship_id'],
            'program_details.specialization_1'       => ['required', 'string', 'max:255'],
            'program_details.specialization_2'       => ['nullable', 'string', 'max:255'],
            'program_details.specialization_3'       => ['nullable', 'string', 'max:255'],
            'program_details.university_name'        => ['required', 'string', 'max:255'],
            'program_details.country_name'           => ['required', 'string', 'max:100'],
            'program_details.tuition_fee'            => ['nullable', 'numeric', 'min:0'],
            'program_details.has_active_program'     => ['required', 'boolean'],
            'program_details.current_semester_number' => ['nullable', 'integer', 'min:1', 'max:20'],
            'program_details.cgpa'                   => ['nullable', 'numeric', 'min:0', 'max:4'],
            'program_details.cgpa_out_of'            => ['nullable', 'numeric', 'min:0'],
            'program_details.terms_and_condition'    => ['required', 'accepted'],

            // file key name you'll send from Postman: program_details.offer_letter
            'program_details.offer_letter'           => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            DB::beginTransaction();

            // Validate the scholarship is open & active
            $scholarship = Scholarship::where('scholarship_id', $data['program_details']['scholarship_id'])
                ->where('is_active', true)
                ->where('opening_date', '<=', now())
                ->where('closing_date', '>=', now())
                ->first();

            if (!$scholarship) {
                return response()->json(['message' => 'Selected scholarship is not available'], 422);
            }

            // Create application first (no file yet)
            $application = ApplicantApplication::create([
                'applicant_id'            => $applicant->applicant_id,
                'scholarship_id'          => $data['program_details']['scholarship_id'],
                'specialization_1'        => $data['program_details']['specialization_1'],
                'specialization_2'        => $data['program_details']['specialization_2'] ?? null,
                'specialization_3'        => $data['program_details']['specialization_3'] ?? null,
                'university_name'         => $data['program_details']['university_name'],
                'country_name'            => $data['program_details']['country_name'],
                'tuition_fee'             => $data['program_details']['tuition_fee'] ?? null,
                'has_active_program'      => $data['program_details']['has_active_program'],
                'current_semester_number' => $data['program_details']['current_semester_number'] ?? null,
                'cgpa'                    => $data['program_details']['cgpa'] ?? null,
                'cgpa_out_of'             => $data['program_details']['cgpa_out_of'] ?? null,
                'terms_and_condition'     => $data['program_details']['terms_and_condition'],
            ]);

            // Handle offer letter (optional)
            if ($request->hasFile('program_details.offer_letter')) {
                $file     = $request->file('program_details.offer_letter');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

                // nice structured path per applicant + application
                $path = $file->storeAs(
                    "applications/{$applicant->applicant_id}/{$application->application_id}/offer-letter",
                    $filename,
                    's3'
                );

                $application->update([
                    'offer_letter_file' => config('filesystems.disks.s3.url') . '/' . $path
                ]);
            }

            // Initial status = ENROLLED
            ApplicantApplicationStatus::create([
                'application_id' => $application->application_id,
                'status_name'    => ApplicationStatus::ENROLLED->value,
                'date'           => now(),
                'comment'        => 'Application submitted',
            ]);

            DB::commit();

            return response()->json([
                'message'     => 'Application submitted successfully',
                'application' => $application->load(['currentStatus', 'scholarship']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit application',
                'error'   => $e->getMessage(),
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
        $application = ApplicantApplication::with('currentStatus')->findOrFail($applicationId);

        $data = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === ApplicationStatus::REJECTED->value && !$application->canBeRejected()) {
            return response()->json([
                'message' => 'Cannot reject application after final approval'
            ], 422);
        }

        try {
            DB::beginTransaction();

            ApplicantApplicationStatus::create([
                'application_id' => $applicationId,
                'status_name' => $data['status'],
                'date' => now(),
                'comment' => $data['comment'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Application status updated successfully',
                'current_status' => $application->fresh()->currentStatus
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update application status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get application with full details
     */
    public function show(Request $request, $applicationId)
    {
        $application = ApplicantApplication::with([
            'applicant.user',
            'applicant.qualifications',
            'scholarship',
            'statuses',
            'currentStatus'
        ])->findOrFail($applicationId);

        if (
            $request->user()->role->value !== 'admin' &&
            $application->applicant_id !== $request->user()->applicant->applicant_id
        ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($application);
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
            'currentStatus',
            'applicant.qualifications',
            'applicant'
        ])->where('applicant_id', $applicant->applicant_id)
            ->get();

        return response()->json($applications);
    }

    /**
     * Get all applications (Admin only)
     */
    public function getAllApplications(Request $request)
    {
        if ($request->user()->role->value !== 'admin') {
            return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
        }

        $applications = ApplicantApplication::with([
            'applicant.user',
            'applicant.qualifications',
            'scholarship',
            'currentStatus'
        ])->get();

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

            $application->statuses()->delete();

            if ($application->offer_letter_file) {
                Storage::disk('s3')->delete($application->offer_letter_file);
            }

            $application->delete();

            DB::commit();

            return response()->json([
                'message' => 'Application deleted successfully'
            ]);
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

        $stats = [
            'total_applications' => ApplicantApplication::count(),
            'enrolled' => ApplicantApplication::whereHas('currentStatus', function ($q) {
                $q->where('status_name', ApplicationStatus::ENROLLED->value);
            })->count(),
            'first_approval' => ApplicantApplication::whereHas('currentStatus', function ($q) {
                $q->where('status_name', ApplicationStatus::FIRST_APPROVAL->value);
            })->count(),
            'second_approval' => ApplicantApplication::whereHas('currentStatus', function ($q) {
                $q->where('status_name', ApplicationStatus::SECOND_APPROVAL->value);
            })->count(),
            'final_approval' => ApplicantApplication::whereHas('currentStatus', function ($q) {
                $q->where('status_name', ApplicationStatus::FINAL_APPROVAL->value);
            })->count(),
            'rejected' => ApplicantApplication::whereHas('currentStatus', function ($q) {
                $q->where('status_name', ApplicationStatus::REJECTED->value);
            })->count(),
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
}
