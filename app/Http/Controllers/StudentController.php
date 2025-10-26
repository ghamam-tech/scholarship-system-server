<?php

namespace App\Http\Controllers;

use App\Models\ApprovedApplicantApplication;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\User;
use App\Models\Applicant;
use App\Models\UserStatus;
use App\Models\Qualification;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function statusSummary(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $latestStatus = $user->currentStatus;
        $applicant = $user->applicant;

        $response = [
            'latest_status' => $latestStatus?->status_name,
            'is_completed' => $applicant?->is_completed ?? false,
        ];

        $approvedApplication = ApprovedApplicantApplication::with('scholarship')
            ->where('user_id', $user->user_id)
            ->where('has_accepted_scholarship', false)
            ->latest('created_at')
            ->first();

        if ($approvedApplication) {
            $response['apprroved_application_id'] = $approvedApplication->approved_application_id;

            if ($approvedApplication->scholarship) {
                $scholarship = $approvedApplication->scholarship;

                $response['scholarship'] = [
                    'id' => $scholarship->scholarship_id,
                    'name' => $scholarship->scholarship_name,
                    'type' => $scholarship->scholarship_type,
                    'description' => $scholarship->description,
                    'benefits' => $approvedApplication->benefits,
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * Student-only: Update profile data, keeping existing files unless replacements are provided.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $applicant = $user->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $rules = [
            'personal_info' => ['sometimes', 'array'],
            'personal_info.ar_name' => ['required_with:personal_info', 'string', 'max:255'],
            'personal_info.en_name' => ['required_with:personal_info', 'string', 'max:255'],
            'personal_info.nationality' => ['required_with:personal_info', 'string', 'max:100'],
            'personal_info.gender' => ['required_with:personal_info', 'string', 'in:male,female'],
            'personal_info.place_of_birth' => ['required_with:personal_info', 'string', 'max:255'],
            'personal_info.phone' => ['required_with:personal_info', 'string', 'max:20'],
            'personal_info.passport_number' => ['required_with:personal_info', 'string', 'max:50', 'unique:applicants,passport_number,' . $applicant->applicant_id . ',applicant_id'],
            'personal_info.passport_expiry' => ['nullable', 'date'],
            'personal_info.date_of_birth' => ['required_with:personal_info', 'string'],
            'personal_info.parent_contact_name' => ['required_with:personal_info', 'string', 'max:255'],
            'personal_info.parent_contact_phone' => ['required_with:personal_info', 'string', 'max:20'],
            'personal_info.residence_country' => ['required_with:personal_info', 'string', 'max:100'],
            'personal_info.language' => ['required_with:personal_info', 'string', 'max:50'],
            'personal_info.is_studied_in_saudi' => ['required_with:personal_info', 'boolean'],
            'personal_info.tahseeli_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'personal_info.qudorat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'academic_info' => ['sometimes', 'array'],
            'academic_info.qualifications' => ['sometimes', 'array'],
            'academic_info.qualifications.*.qualification_id' => ['sometimes', 'integer', 'exists:qualifications,qualification_id'],
            'academic_info.qualifications.*.qualification_type' => ['sometimes', Rule::in(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other'])],
            'academic_info.qualifications.*.institute_name' => ['sometimes', 'string', 'max:255'],
            'academic_info.qualifications.*.country' => ['sometimes', 'string', 'max:100'],
            'academic_info.qualifications.*.year_of_graduation' => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'academic_info.qualifications.*.cgpa' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'academic_info.qualifications.*.cgpa_out_of' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'academic_info.qualifications.*.language_of_study' => ['sometimes', 'nullable', 'string', 'max:100'],
            'academic_info.qualifications.*.specialization' => ['sometimes', 'nullable', 'string', 'max:255'],
            'academic_info.qualifications.*.research_title' => ['sometimes', 'nullable', 'string', 'max:500'],
            'academic_info.qualifications.*.document_file' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],

            'passport_copy' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'personal_image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'tahsili_file' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'qudorat_file' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'volunteering_certificate' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ];

        $data = $request->validate($rules);

        try {
            DB::beginTransaction();

            if (isset($data['personal_info'])) {
                $applicant->update(array_merge($data['personal_info'], ['is_completed' => true]));
            }

            $this->handleDocumentUploads($request, $applicant);

            if (isset($data['academic_info']['qualifications'])) {
                $this->syncQualifications($request, $user, $data['academic_info']['qualifications']);
            }

            DB::commit();

            $applicant->refresh();
            $applicant->setRelation(
                'qualifications',
                $user->qualifications()->orderBy('qualification_type')->get()
            );

            return response()->json([
                'message' => 'Student profile updated successfully',
                'applicant' => $applicant,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update student profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Student-only: Retrieve applicant profile with qualifications.
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $applicant = $user->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $applicant->load('user');
        $applicant->setRelation(
            'qualifications',
            $user->qualifications()->orderBy('qualification_type')->get()
        );

        return response()->json(['applicant' => $applicant]);
    }

    private function handleDocumentUploads(Request $request, Applicant $applicant): void
    {
        $documentFields = [
            'passport_copy' => 'passport_copy_img',
            'personal_image' => 'personal_image',
            'tahsili_file' => 'tahsili_file',
            'qudorat_file' => 'qudorat_file',
            'volunteering_certificate' => 'volunteering_certificate_file',
        ];

        foreach ($documentFields as $requestField => $modelField) {
            if ($request->hasFile($requestField)) {
                if ($applicant->$modelField) {
                    Storage::disk('s3')->delete($applicant->$modelField);
                }

                $file = $request->file($requestField);
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs(
                    "applicants/{$applicant->applicant_id}/documents",
                    $filename,
                    's3'
                );

                $applicant->update([$modelField => $path]);
            }
        }
    }

    private function syncQualifications(Request $request, User $user, array $qualifications): void
    {
        foreach ($qualifications as $index => $qualificationData) {
            $fileKey = "academic_info.qualifications.$index.document_file";
            $qualificationId = $qualificationData['qualification_id'] ?? null;
            $qualification = null;

            if ($qualificationId) {
                $qualification = $user->qualifications()
                    ->where('qualification_id', $qualificationId)
                    ->first();

                if (!$qualification) {
                    throw ValidationException::withMessages([
                        "academic_info.qualifications.$index.qualification_id" => ['Qualification not found for this student.'],
                    ]);
                }
            }

            if ($qualification) {
                $payload = $this->extractQualificationPayload($qualificationData);

                if ($request->hasFile($fileKey)) {
                    if ($qualification->document_file) {
                        Storage::disk('s3')->delete($qualification->document_file);
                    }

                    $file = $request->file($fileKey);
                    $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $payload['document_file'] = $file->storeAs(
                        "users/{$user->user_id}/qualifications",
                        $filename,
                        's3'
                    );
                }

                if (!empty($payload)) {
                    $qualification->update($payload);
                }

                continue;
            }

            $this->createQualification($request, $user, $qualificationData, $index);
        }
    }

    private function extractQualificationPayload(array $data): array
    {
        $fields = [
            'qualification_type',
            'institute_name',
            'country',
            'year_of_graduation',
            'cgpa',
            'cgpa_out_of',
            'language_of_study',
            'specialization',
            'research_title',
        ];

        $payload = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    private function createQualification(Request $request, User $user, array $data, int $index): void
    {
        $validated = Validator::make($data, [
            'qualification_type' => ['required', Rule::in(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other'])],
            'institute_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'year_of_graduation' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'cgpa' => ['nullable', 'numeric', 'min:0'],
            'cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
            'language_of_study' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'research_title' => ['nullable', 'string', 'max:500'],
        ])->validate();

        $fileKey = "academic_info.qualifications.$index.document_file";

        if (!$request->hasFile($fileKey)) {
            throw ValidationException::withMessages([
                $fileKey => ['Document file is required when creating a new qualification.'],
            ]);
        }

        $file = $request->file($fileKey);
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $documentPath = $file->storeAs(
            "users/{$user->user_id}/qualifications",
            $filename,
            's3'
        );

        Qualification::create([
            'user_id' => $user->user_id,
            'qualification_type' => $validated['qualification_type'],
            'institute_name' => $validated['institute_name'],
            'country' => $validated['country'],
            'year_of_graduation' => $validated['year_of_graduation'],
            'cgpa' => $validated['cgpa'] ?? null,
            'cgpa_out_of' => $validated['cgpa_out_of'] ?? null,
            'language_of_study' => $validated['language_of_study'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'research_title' => $validated['research_title'] ?? null,
            'document_file' => $documentPath,
        ]);
    }

    /**
     * Admin-only: List students with summary information.
     */
    public function index(Request $request)
    {
        $students = Student::with([
            'applicant',
            'country',
            'user.currentStatus',
        ])->orderBy('student_id')->get();

        $data = $students->map(function (Student $student) {
            return [
                'student_id' => $student->student_id,
                'name_ar' => optional($student->applicant)->ar_name,
                'country_of_study' => optional($student->country)->country_name,
                'cgpa' => 4,
                'kpi' => 'ok',
                'latest_status' => $student->user?->currentStatus?->status_name,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Admin-only: Show detailed student information.
     */
    public function show(Request $request, $studentId)
    {
        $student = Student::with([
            'user.statuses' => function ($query) {
                $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
            },
            'user.applicant.qualifications',
            'applicant',
            'country',
            'university',
            'approvedApplication.scholarship',
        ])->findOrFail($studentId);

        $user = $student->user;
        $applicant = $student->applicant ?? $user?->applicant;
        $latestStatus = $user?->currentStatus;
        $statuses = $user?->statuses ?? collect();

        $personal = [
            'nameEnglish' => $applicant?->en_name,
            'nameArabic' => $applicant?->ar_name,
            'nationality' => $applicant?->nationality,
            'gender' => $applicant?->gender,
            'dateOfBirth' => $applicant?->date_of_birth,
            'placeOfBirth' => $applicant?->place_of_birth,
            'email' => $user?->email,
            'phone' => $applicant?->phone,
            'residenceCountry' => $applicant?->residence_country,
            'passportNumber' => $applicant?->passport_number,
            'passportExpiry' => $applicant?->passport_expiry,
            'parentContactName' => $applicant?->parent_contact_name,
            'parentContactNumber' => $applicant?->parent_contact_phone,
        ];

        $qualifications = $user
            ? $user->qualifications()
                ->orderBy('qualification_type')
                ->get()
                ->map(function (Qualification $qualification) use ($applicant) {
                    $qualificationData = [
                        'qualification_type' => $qualification->qualification_type,
                        'institute_name' => $qualification->institute_name,
                        'year_of_graduation' => $qualification->year_of_graduation,
                        'cgpa' => $qualification->cgpa,
                        'cgpa_out_of' => $qualification->cgpa_out_of,
                        'language_of_study' => $qualification->language_of_study,
                        'specialization' => $qualification->specialization,
                        'research_title' => $qualification->research_title,
                        'document_file' => $qualification->document_file,
                    ];

                    if (
                        $applicant?->is_studied_in_saudi
                        && $qualification->qualification_type === 'high_school'
                    ) {
                        $qualificationData['studiedInSaudi'] = true;
                        $qualificationData['qudoratPercentage'] = $applicant->qudorat_percentage;
                        $qualificationData['tahseeliPercentage'] = $applicant->tahseeli_percentage;
                    }

                    return $qualificationData;
                })
                ->values()
            : collect();

        $program = [
            'started' => true,
            'country' => $student->country?->country_name,
            'university' => $student->university?->university_name,
            'language' => $student->language_of_study,
            'specialization' => $student->specialization,
            'yearlyTuition' => $student->yearly_tuition_fees,
            'studyPeriod' => $student->study_period,
            'numberOfSemesters' => $student->total_semesters_number,
            'currentSemester' => $student->current_semester_number,
            'currentCGPA' => 3.8,
        ];

        $scholarship = null;
        $approvedApplication = $student->approvedApplication;

        if ($approvedApplication && $approvedApplication->scholarship) {
            $scholarshipModel = $approvedApplication->scholarship;
            $scholarship = [
                'id' => $scholarshipModel->scholarship_id,
                'name' => $scholarshipModel->scholarship_name,
                'type' => $scholarshipModel->scholarship_type,
                'benefits' => $approvedApplication->benefits,
            ];
        }

        $statusTrail = $statuses->map(function (UserStatus $status) {
            return [
                'status_name' => $status->status_name,
                'date' => $status->date,
                'comment' => $status->comment,
                'status' => 'completed',
            ];
        })->values();

        return response()->json([
            'data' => [
                'id' => $student->student_id,
                'status' => $latestStatus?->status_name,
                'enrollmentDate' => now()->toDateString(),
                'personal' => $personal,
                'academic' => [
                    'qualifications' => $qualifications,
                ],
                'program' => $program,
                'semesters' => [],
                'scholarship' => $scholarship,
                'kpi' => [
                    'academicScore' => 85,
                    'attendanceScore' => 92,
                    'engagementScore' => 78,
                    'overallScore' => 85,
                ],
                'statusTrail' => $statusTrail,
            ]
        ], 200);
    }

    /**
     * Admin-only: Issue FIRST_WARNING to a student.
     */
    public function issueFirstWarning(Request $request, $studentId)
    {
        // Admin gate
        $auth = $request->user();
        $roleVal = is_object($auth->role) ? $auth->role->value : (string) $auth->role;
        if ($roleVal !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can issue warnings'], 403);
        }

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            return DB::transaction(function () use ($studentId, $data) {
                $student = Student::with(['user'])->findOrFail($studentId);
                $userId = $student->user_id;

                UserStatus::create([
                    'user_id' => $userId,
                    'status_name' => ApplicationStatus::FIRST_WARNING->value,
                    'date' => now(),
                    'comment' => $data['comment'] ?? 'First warning issued by admin',
                ]);

                return response()->json([
                    'message' => 'First warning issued successfully',
                    'student_id' => $student->student_id,
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to issue first warning',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin-only: Issue SECOND_WARNING to a student.
     */
    public function issueSecondWarning(Request $request, $studentId)
    {
        // Admin gate
        $auth = $request->user();
        $roleVal = is_object($auth->role) ? $auth->role->value : (string) $auth->role;
        if ($roleVal !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can issue warnings'], 403);
        }

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            return DB::transaction(function () use ($studentId, $data) {
                $student = Student::with(['user'])->findOrFail($studentId);
                $userId = $student->user_id;

                UserStatus::create([
                    'user_id' => $userId,
                    'status_name' => ApplicationStatus::SECOND_WARNING->value,
                    'date' => now(),
                    'comment' => $data['comment'] ?? 'Second warning issued by admin',
                ]);

                return response()->json([
                    'message' => 'Second warning issued successfully',
                    'student_id' => $student->student_id,
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to issue second warning',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin-only: Terminate a student.
     * - Adds TERMINATED status
     * - Reverts user role to APPLICANT
     * - Deletes the student record
     */
    public function terminateStudent(Request $request, $studentId)
    {
        // Admin gate
        $auth = $request->user();
        $roleVal = is_object($auth->role) ? $auth->role->value : (string) $auth->role;
        if ($roleVal !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can terminate students'], 403);
        }

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            return DB::transaction(function () use ($studentId, $data) {
                // Load student with user
                $student = Student::with(['user'])->findOrFail($studentId);
                $user = $student->user;

                if (!$user) {
                    return response()->json(['message' => 'Linked user not found for this student'], 422);
                }

                // 1) Status trail: TERMINATED
                UserStatus::create([
                    'user_id' => $user->user_id,
                    'status_name' => ApplicationStatus::TERMINATED->value,
                    'date' => now(),
                    'comment' => $data['comment'] ?? 'Student terminated by admin',
                ]);

                // 2) Revert user role to APPLICANT
                $user->role = is_object($user->role) ? UserRole::APPLICANT : UserRole::APPLICANT->value;
                $user->save();

                // 3) Delete the student record
                $student->delete();

                // If you want to unarchive the Applicant profile on termination, uncomment:
                // $applicant = Applicant::where('user_id', $user->user_id)->first();
                // if ($applicant) {
                //     $applicant->is_archive = false;
                //     $applicant->save();
                // }

                return response()->json([
                    'message' => 'Student terminated successfully. Role reverted to applicant and student record removed.',
                    'user_id' => $user->user_id,
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to terminate student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin-only: Mark a student as GRADUATED.
     * - Adds GRADUATED status to status trail
     * - Reverts user role back to APPLICANT
     * - Keeps the Student row (for history/alumni) — no deletion
     */
    public function graduateStudent(Request $request, $studentId)
    {
        // Admin gate
        $auth = $request->user();
        $roleVal = is_object($auth->role) ? $auth->role->value : (string) $auth->role;
        if ($roleVal !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can graduate students'], 403);
        }

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            return DB::transaction(function () use ($studentId, $data) {
                // Load student with linked user
                $student = Student::with('user')->findOrFail($studentId);
                $user = $student->user;

                if (!$user) {
                    return response()->json(['message' => 'Linked user not found for this student'], 422);
                }

                // 1) Append GRADUATED to status trail
                UserStatus::create([
                    'user_id' => $user->user_id,
                    'status_name' => ApplicationStatus::GRADUATED->value,
                    'date' => now(),
                    'comment' => $data['comment'] ?? 'Student graduated (set by admin)',
                ]);

                // 2) Revert user role to APPLICANT
                $user->role = is_object($user->role) ? UserRole::APPLICANT : UserRole::APPLICANT->value;
                $user->save();

                // 3) Unarchive applicant profile after graduation:
                $applicant = Applicant::where('user_id', $user->user_id)->first();
                if ($applicant) {
                    $applicant->is_archive = false;
                    $applicant->save();
                }

                return response()->json([
                    'message' => 'Student marked as graduated. Role reverted to applicant.',
                    'studentId' => $student->student_id,
                    'userId' => $user->user_id,
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to graduate student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function requestMeeting(Request $request, $studentId)
    {
        // Admin gate
        $auth = $request->user();
        $roleVal = is_object($auth->role) ? $auth->role->value : (string) $auth->role;
        if ($roleVal !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can request meetings'], 403);
        }

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            return DB::transaction(function () use ($studentId, $data) {
                $student = Student::with('user')->findOrFail($studentId);
                $user = $student->user;

                if (!$user) {
                    return response()->json(['message' => 'Linked user not found for this student'], 422);
                }

                UserStatus::create([
                    'user_id' => $user->user_id,
                    'status_name' => ApplicationStatus::MEETING_REQUESTED->value,
                    'date' => now(),
                    'comment' => $data['comment'] ?? 'Meeting requested by admin',
                ]);

                return response()->json([
                    'message' => 'Meeting requested.',
                    'studentId' => $student->student_id,
                    'userId' => $user->user_id,
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to request meeting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin-only: Add a student manually.
     *
     * Flow:
     * 1) Create User (role=STUDENT)
     * 2) Create Applicant (names, user_id, is_archive=true)
     * 3) Create ApprovedApplicantApplication (benefits, scholarship_id, user_id) — has_accepted_scholarship=true
     * 4) Create Student (user_id, applicant_id, approved_application_id)
     * 5) Add status trail "added_manually"
     */
    public function addStudentManually(Request $request)
    {
        // Admin gate
        $auth = $request->user();
        $roleVal = is_object($auth->role) ? $auth->role->value : (string) $auth->role;
        if ($roleVal !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can add students manually'], 403);
        }

        // Validate input
        $data = $request->validate([
            'ar_name' => ['required', 'string', 'max:255'],
            'en_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'scholarship_id' => ['required', 'exists:scholarships,scholarship_id'],
            'benefits' => ['nullable'], // array|object|string. If you cast in the model, array is fine.
            'comment' => ['nullable', 'string', 'max:1000'], // status trail note
        ]);

        try {
            return DB::transaction(function () use ($data) {
                // 1) Create User as STUDENT
                $user = User::create([
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    // Support enum-cast or plain string
                    'role' => is_subclass_of(UserRole::class, \BackedEnum::class)
                        ? UserRole::STUDENT
                        : UserRole::STUDENT->value,
                ]);

                // 2) Create Applicant (archived)
                $applicant = Applicant::create([
                    'user_id' => $user->user_id,
                    'ar_name' => $data['ar_name'],
                    'en_name' => $data['en_name'],
                    'is_archive' => true, // keep consistent with previous codebase usage
                ]);

                // Ensure scholarship exists & optionally active
                $scholarship = Scholarship::where('scholarship_id', $data['scholarship_id'])->firstOrFail();

                // Normalize benefits if needed (store as JSON string if your model isn't cast)
                $benefitsValue = $data['benefits'] ?? null;
                if (is_array($benefitsValue) || is_object($benefitsValue)) {
                    $benefitsValue = json_encode($benefitsValue, JSON_UNESCAPED_UNICODE);
                }

                // 3) Create ApprovedApplicantApplication
                // Note: application_id is omitted on purpose for manual add (assumed nullable)
                $approval = ApprovedApplicantApplication::create([
                    'benefits' => $benefitsValue,
                    'has_accepted_scholarship' => false, // manual add ⇒ considered accepted
                    'scholarship_id' => $scholarship->scholarship_id,
                    'application_id' => null, // <-- if NOT nullable in your schema, ping me to adjust
                    'user_id' => $user->user_id,
                ]);

                // 4) Create Student
                $student = Student::create([
                    'user_id' => $user->user_id,
                    'applicant_id' => $applicant->applicant_id,
                    'approved_application_id' => $approval->approved_application_id,
                ]);

                // 5) Status trail: ADDED_MANUALLY
                // If you later add ApplicationStatus::ADDED_MANUALLY, replace the string below.
                $statusName = method_exists(ApplicationStatus::class, 'ADDED_MANUALLY')
                    ? ApplicationStatus::ADDED_MANUALLY->value
                    : ApplicationStatus::ADDED_MANUALLY;

                UserStatus::create([
                    'user_id' => $user->user_id,
                    'status_name' => $statusName,
                    'date' => now(),
                    'comment' => $data['comment'] ?? 'Student added manually by admin',
                ]);

                // Optional eager-load for response
                $student->load(['user']);
                $approval->load(['scholarship', 'user']);

                return response()->json([
                    'message' => 'Student added manually successfully',
                    'user' => [
                        'user_id' => $user->user_id,
                        'email' => $user->email,
                        'role' => is_object($user->role) ? $user->role->value : (string) $user->role,
                    ],
                    'applicant' => $applicant,
                    'approved_application' => $approval,
                    'student' => $student,
                ], 201);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to add student manually',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
