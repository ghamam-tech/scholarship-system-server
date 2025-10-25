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

class StudentController extends Controller
{
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
                    'has_accepted_scholarship' => true, // manual add ⇒ considered accepted
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
