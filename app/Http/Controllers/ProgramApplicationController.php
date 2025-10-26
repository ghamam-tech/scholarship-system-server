<?php

namespace App\Http\Controllers;

use App\Models\ProgramApplication;
use App\Models\Program;
use App\Models\Student;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProgramApplicationController extends Controller
{

    /**
     * Admin: Get students available for invitation
     */
    public function getStudentsForInvitation(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can view students for invitation'], 403);
        }

        $students = Student::with(['user', 'applicant', 'approvedApplication.scholarship'])
            ->whereHas('user')
            ->whereHas('applicant')
            ->get();

        return response()->json([
            'students' => $students->map(function ($student) {
                return [
                    'student_id' => $student->student_id,
                    'name' => $student->applicant?->ar_name ?? $student->applicant?->en_name ?? 'N/A',
                    'email' => $student->user?->email ?? 'N/A',
                    'scholarship_id' => $student->approvedApplication?->scholarship?->scholarship_id ?? 'N/A',
                    'scholarship_name' => $student->approvedApplication?->scholarship?->scholarship_name ?? 'N/A',
                ];
            })
        ]);
    }

    /**
     * Admin: Invite multiple students to program
     */
    public function inviteMultipleStudents(Request $request, $programId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can invite students'], 403);
        }

        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,student_id'],
        ]);

        // Validate that all students have proper relationships
        $invalidStudents = [];
        foreach ($data['student_ids'] as $studentId) {
            $student = Student::with(['user', 'applicant'])->find($studentId);
            if (!$student || !$student->user || !$student->applicant) {
                $invalidStudents[] = $studentId;
            }
        }

        if (!empty($invalidStudents)) {
            return response()->json([
                'message' => 'Some students have missing user or applicant relationships',
                'invalid_student_ids' => $invalidStudents
            ], 422);
        }

        $program = Program::find($programId);
        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        try {
            DB::beginTransaction();

            $invitedApplications = [];
            $alreadyInvited = [];

            foreach ($data['student_ids'] as $studentId) {
                // Check if invitation already exists
                $existingApplication = ProgramApplication::where('student_id', $studentId)
                    ->where('program_id', $programId)
                    ->first();

                if ($existingApplication) {
                    $alreadyInvited[] = $existingApplication->load(['student.user', 'student.applicant']);
                    continue;
                }

                $application = ProgramApplication::create([
                    'student_id' => $studentId,
                    'program_id' => $programId,
                    'application_status' => 'invite'
                ]);

                $invitedApplications[] = $application->load(['student.user', 'program']);
            }

            DB::commit();

            // Get all existing applications for this program
            $allExistingApplications = ProgramApplication::with(['student.user', 'student.applicant'])
                ->where('program_id', $programId)
                ->get();

            return response()->json([
                'message' => 'Invitations sent successfully',
                'invited_count' => count($invitedApplications),
                'already_invited_count' => count($alreadyInvited),
                'applications' => collect($invitedApplications)->map(function ($application) {
                    return [
                        'application_program_id' => $application->application_program_id,
                        'student_id' => $application->student_id,
                        'ar_name' => $application->student?->applicant?->ar_name ?? 'N/A',
                        'email' => $application->student?->user?->email ?? 'N/A',
                        'status' => $application->application_status,
                    ];
                }),
                'already_invited_student_ids' => collect($alreadyInvited)->map(function ($application) {
                    return [
                        'application_program_id' => $application->application_program_id,
                        'student_id' => $application->student_id,
                        'ar_name' => $application->student?->applicant?->ar_name ?? 'N/A',
                        'email' => $application->student?->user?->email ?? 'N/A',
                        'status' => $application->application_status,
                    ];
                }),
                'all_program_applications' => $allExistingApplications->map(function ($application) {
                    return [
                        'application_program_id' => $application->application_program_id,
                        'student_id' => $application->student_id,
                        'ar_name' => $application->student?->applicant?->ar_name ?? 'N/A',
                        'email' => $application->student?->user?->email ?? 'N/A',
                        'status' => $application->application_status,
                    ];
                })
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to send invitations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Student: Accept invitation
     */
    public function acceptInvitation(Request $request, $applicationId)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can accept invitations'], 403);
        }

        $application = ProgramApplication::with(['student.user', 'program'])->find($applicationId);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Check if the student owns this application
        if ($application->student->user_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized access to this application'], 403);
        }

        // Check if application is in invite status
        if ($application->application_status !== 'invite') {
            return response()->json(['message' => 'Application is not in invite status'], 400);
        }

        try {
            $application->update(['application_status' => 'accepted']);

            return response()->json([
                'message' => 'Invitation accepted successfully',
                'application' => $application
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to accept invitation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Student: Reject invitation with excuse
     */
    public function rejectInvitation(Request $request, $applicationId)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can reject invitations'], 403);
        }

        // Debug: Check what we're receiving
        Log::info('Content-Type:', ['content_type' => $request->header('Content-Type')]);
        Log::info('All data:', $request->all());
        Log::info('Files:', $request->allFiles());
        Log::info('Input method:', ['method' => $request->method()]);

        // Handle both JSON and form data
        $data = $request->validate([
            'excuse_reason' => ['required', 'string', 'max:1000'],
            'excuse_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $application = ProgramApplication::with(['student.user', 'program'])->find($applicationId);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Check if the student owns this application
        if ($application->student->user_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized access to this application'], 403);
        }

        // Check if application is in invite status
        if ($application->application_status !== 'invite') {
            return response()->json(['message' => 'Application is not in invite status'], 400);
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'application_status' => 'excuse',
                'excuse_reason' => $data['excuse_reason']
            ];

            // Handle excuse file upload
            if ($request->hasFile('excuse_file')) {
                $excuseFile = $request->file('excuse_file');
                $excusePath = $excuseFile->store('program_applications/excuses', 'public');
                $updateData['excuse_file'] = $excusePath;
            }

            $application->update($updateData);

            DB::commit();

            return response()->json([
                'message' => 'Invitation rejected with excuse',
                'application' => $application
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reject invitation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Approve student excuse
     */
    public function approveExcuse(Request $request, $applicationId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can approve excuses'], 403);
        }

        $application = ProgramApplication::with(['student.user', 'program'])->find($applicationId);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Check if application is in excuse status
        if ($application->application_status !== 'excuse') {
            return response()->json(['message' => 'Application is not in excuse status'], 400);
        }

        try {
            $application->update(['application_status' => 'approved_excuse']);

            return response()->json([
                'message' => 'Excuse approved successfully',
                'application' => $application
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve excuse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Reject student excuse
     */
    public function rejectExcuse(Request $request, $applicationId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can reject excuses'], 403);
        }

        $application = ProgramApplication::with(['student.user', 'program'])->find($applicationId);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Check if application is in excuse status
        if ($application->application_status !== 'excuse') {
            return response()->json(['message' => 'Application is not in excuse status'], 400);
        }

        try {
            $application->update(['application_status' => 'rejected_excuse']);

            return response()->json([
                'message' => 'Excuse rejected successfully',
                'application' => $application
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reject excuse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Student: QR Code attendance
     */
    public function qrAttendance(Request $request, $applicationId)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can mark attendance'], 403);
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $application = ProgramApplication::with(['student.user', 'program'])->find($applicationId);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Check if the student owns this application
        if ($application->student->user_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized access to this application'], 403);
        }

        // Check if application is in accepted status
        if ($application->application_status !== 'accepted') {
            return response()->json(['message' => 'Application must be accepted before marking attendance'], 400);
        }

        // Verify student credentials
        if ($user->email !== $data['email'] || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        try {
            $application->update(['application_status' => 'attend']);

            return response()->json([
                'message' => 'Attendance marked successfully',
                'application' => $application
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all program applications
     */
    public function getProgramApplications(Request $request, $programId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can view program applications'], 403);
        }

        $program = Program::find($programId);
        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        $applications = ProgramApplication::with(['student.user', 'student.applicant', 'student.approvedApplication.scholarship', 'student.approvedApplication.application'])
            ->where('program_id', $programId)
            ->whereHas('student') // Only get applications with valid students
            ->get();

        return response()->json([
            'program' => [
                'program_id' => $program->program_id,
                'title' => $program->title
            ],
            'applications' => $applications->map(function ($application) {
                return [
                    'application_id' => $application->application_program_id,
                    'student_id' => $application->student_id,
                    'name' => $application->student?->applicant?->ar_name ?? $application->student?->applicant?->en_name ?? 'N/A',
                    'email' => $application->student?->user?->email ?? 'N/A',
                    'status' => $application->application_status,
                    'scholarship_id' => $application->student?->approvedApplication?->scholarship_id ?? null,
                    'scholarship_name' => $application->student?->approvedApplication?->scholarship?->scholarship_name ?? 'N/A',
                ];
            })
        ]);
    }

    /**
     * Admin: Delete program application
     */
    public function deleteApplication(Request $request, $applicationId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can delete applications'], 403);
        }

        $application = ProgramApplication::with(['student.user', 'student.applicant', 'program'])
            ->find($applicationId);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        try {
            // Delete excuse file if exists
            if ($application->excuse_file && Storage::disk('public')->exists($application->excuse_file)) {
                Storage::disk('public')->delete($application->excuse_file);
            }

            $application->delete();

            return response()->json([
                'message' => 'Application deleted successfully',
                'deleted_application' => [
                    'application_program_id' => $applicationId,
                    'student_id' => $application->student_id,
                    'ar_name' => $application->student->applicant->ar_name ?? 'N/A',
                    'email' => $application->student->user->email,
                    'program_title' => $application->program->title,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get excuse details for an application
     */
    public function getExcuseDetails(Request $request, $applicationId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can view excuse details'], 403);
        }

        $application = ProgramApplication::with(['student.user', 'student.applicant', 'program'])
            ->find($applicationId);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Check if application has excuse
        if ($application->application_status !== 'excuse') {
            return response()->json(['message' => 'Application does not have an excuse'], 400);
        }

        return response()->json([
            'application' => [
                'application_id' => $application->application_program_id,
                'excuse_reason' => $application->excuse_reason,
                'excuse_file' => $application->excuse_file,
                'excuse_file_url' => $application->excuse_file ? asset('storage/' . $application->excuse_file) : null,
                'email' => $application->student->user->email,
                'ar_name' => $application->student->applicant->ar_name,
                'status' => $application->application_status,
                'program_title' => $application->program->title,
                'submitted_at' => $application->updated_at
            ]
        ]);
    }

    /**
     * Student: Get my program applications
     */
    public function getMyApplications(Request $request)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can view their applications'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $applications = ProgramApplication::with(['program'])
            ->where('student_id', $student->student_id)
            ->get();

        return response()->json([
            'applications' => $applications
        ]);
    }

    /**
     * Student: Get all programs that the student has applications for
     */
    public function getProgramsForStudent(Request $request)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can view their programs'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $applications = ProgramApplication::with(['program'])
            ->where('student_id', $student->student_id)
            ->get();

        $programs = $applications->map(function ($application) {
            // Get enrollment count for this program
            $enrollmentCount = ProgramApplication::where('program_id', $application->program->program_id)
                ->where('application_status', 'accepted')
                ->count();

            // Get total applications for this program
            $totalApplications = ProgramApplication::where('program_id', $application->program->program_id)->count();

            return [
                'program_id' => $application->program->program_id,
                'title' => $application->program->title,
                'description' => $application->program->discription,
                'date' => $application->program->date,
                'location' => $application->program->location,
                'country' => $application->program->country,
                'category' => $application->program->category,
                'program_status' => $application->program->program_status,
                'start_date' => $application->program->start_date,
                'end_date' => $application->program->end_date,


                // Program image and QR
                'image_file' => $application->program->image_file,
                'image_url' => $application->program->image_file ? asset('storage/' . $application->program->image_file) : null,



                'enrollment_text' => $enrollmentCount . ' enrolled',

                // Application details
                'application_status' => $application->application_status,
                'application_id' => $application->application_program_id,



            ];
        });

        return response()->json([
            'student' => [
                'student_id' => $student->student_id,
                'name' => $student->applicant?->ar_name ?? $student->applicant?->en_name ?? 'N/A',
                'email' => $user->email,
            ],
            'programs' => $programs,
            'total_programs' => $programs->count()
        ]);
    }

    /**
     * Get program by ID
     */
    public function getProgramById(Request $request, $programId)
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $program = Program::find($programId);
        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        // Get enrollment count for this program
        $enrollmentCount = ProgramApplication::where('program_id', $programId)
            ->where('application_status', 'accepted')
            ->count();

        // Get total applications for this program
        $totalApplications = ProgramApplication::where('program_id', $programId)->count();

        // Get program coordinator details
        $coordinator = [
            'name' => $program->program_coordinatior_name,
            'phone' => $program->program_coordinatior_phone,
            'email' => $program->program_coordinatior_email,
        ];

        return response()->json([
            'program' => [
                'program_id' => $program->program_id,
                'title' => $program->title,
                'description' => $program->discription,
                'date' => $program->date,
                'location' => $program->location,
                'country' => $program->country,
                'category' => $program->category,
                'program_status' => $program->program_status,
                'start_date' => $program->start_date,
                'end_date' => $program->end_date,
                'enable_qr_attendance' => $program->enable_qr_attendance,
                'generate_certificates' => $program->generate_certificates,

                // Program coordinator details
                'coordinator' => $coordinator,

                // Program image and QR
                'image_file' => $program->image_file,
                'image_url' => $program->image_file ? asset('storage/' . $program->image_file) : null,
                'qr_url' => $program->qr_url,

                // Enrollment and application statistics
                'enrollment_count' => $enrollmentCount,
                'total_applications' => $totalApplications,
                'enrollment_text' => $enrollmentCount . ' enrolled',

                // Timestamps
                'created_at' => $program->created_at,
                'updated_at' => $program->updated_at,
            ]
        ]);
    }

    /**
     * Get student's program application by Program ID
     */
    public function getMyProgramApplication(Request $request, $programId)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can view their program applications'], 403);
        }

        // Find the student record for this user
        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Find the application for this student and program
        $application = ProgramApplication::with(['student.user', 'student.applicant', 'program', 'student.approvedApplication.scholarship'])
            ->where('program_id', $programId)
            ->where('student_id', $student->student_id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'No application found for this program'], 404);
        }

        return response()->json([
            'application' => [
                'application_id' => $application->application_program_id,
                'student_id' => $application->student_id,
                'program_id' => $application->program_id,
                'application_status' => $application->application_status,
                'excuse_reason' => $application->excuse_reason,
                'excuse_file' => $application->excuse_file,
                'excuse_file_url' => $application->excuse_file ? asset('storage/' . $application->excuse_file) : null,
                'certificate_token' => $application->certificate_token,
                'comment' => $application->comment,
                'created_at' => $application->created_at,
                'updated_at' => $application->updated_at,

                // Student details
                'student' => [
                    'student_id' => $application->student->student_id,
                    'name' => $application->student->applicant?->ar_name ?? $application->student->applicant?->en_name ?? 'N/A',
                    'email' => $application->student->user?->email ?? 'N/A',
                    'specialization' => $application->student->specialization ?? 'N/A',
                    'scholarship_name' => $application->student->approvedApplication?->scholarship?->scholarship_name ?? 'N/A',
                ],

                // Program details
                'program' => [
                    'program_id' => $application->program->program_id,
                    'title' => $application->program->title,
                    'description' => $application->program->discription,
                    'date' => $application->program->date,
                    'location' => $application->program->location,
                    'country' => $application->program->country,
                    'category' => $application->program->category,
                    'program_status' => $application->program->program_status,
                    'start_date' => $application->program->start_date,
                    'end_date' => $application->program->end_date,
                    'enable_qr_attendance' => $application->program->enable_qr_attendance,
                    'generate_certificates' => $application->program->generate_certificates,
                    'coordinator_name' => $application->program->program_coordinatior_name,
                    'coordinator_phone' => $application->program->program_coordinatior_phone,
                    'coordinator_email' => $application->program->program_coordinatior_email,
                    'image_file' => $application->program->image_file,
                    'image_url' => $application->program->image_file ? asset('storage/' . $application->program->image_file) : null,
                    'qr_url' => $application->program->qr_url,
                ]
            ]
        ]);
    }

    /**
     * Student: QR Code attendance with token (requires student authentication)
     */
    public function qrAttendanceWithToken(Request $request, $token)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can mark attendance'], 403);
        }

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,student_id'],
        ]);

        // Find program by QR token
        $program = Program::where('qr_url', $token)->first();

        if (!$program) {
            return response()->json(['message' => 'Invalid QR code'], 404);
        }

        // Check if program is active
        if ($program->program_status !== 'active') {
            return response()->json([
                'message' => 'QR attendance is not available',
                'reason' => 'Program is not active',
                'program_status' => $program->program_status,
                'available_when' => 'Program status is "active"'
            ], 403);
        }

        // Check if QR attendance is enabled
        if (!$program->enable_qr_attendance) {
            return response()->json(['message' => 'QR attendance is not enabled for this program'], 400);
        }

        // Find student record
        $student = Student::find($data['student_id']);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        // Check if the authenticated user owns this student record
        if ($student->user_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized access to this student record'], 403);
        }

        // Find application for this student and program
        $application = ProgramApplication::where('student_id', $student->student_id)
            ->where('program_id', $program->program_id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'No invitation found for this program'], 404);
        }

        // Check if application is in accepted status
        if ($application->application_status !== 'accepted') {
            return response()->json(['message' => 'Application must be accepted before marking attendance'], 400);
        }

        try {
            $application->update(['application_status' => 'attend']);

            return response()->json([
                'message' => 'Attendance marked successfully',
                'application' => $application->load(['student.user', 'program']),
                'student' => [
                    'student_id' => $student->student_id,
                    'name' => $student->en_name ?? $student->ar_name,
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Student: Mark attendance via QR token (requires student authentication)
     * Only students invited to the program can mark attendance
     * Only works when program status is "active"
     */
    public function markAttendanceViaQR(Request $request, $token)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can mark attendance'], 403);
        }

        // Find the student record for this user
        $student = Student::with(['user', 'applicant'])->where('user_id', $user->user_id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Find program by QR token
        $program = Program::where('qr_url', $token)->first();

        if (!$program) {
            return response()->json(['message' => 'Invalid QR code'], 404);
        }

        // Check if program is active
        if ($program->program_status !== 'active') {
            return response()->json([
                'message' => 'QR attendance is not available',
                'reason' => 'Program is not active',
                'program_status' => $program->program_status,
                'available_when' => 'Program status is "active"'
            ], 403);
        }

        // Check if QR attendance is enabled
        if (!$program->enable_qr_attendance) {
            return response()->json(['message' => 'QR attendance is not enabled for this program'], 400);
        }

        // Find application for this student and program
        $application = ProgramApplication::where('student_id', $student->student_id)
            ->where('program_id', $program->program_id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'You are not invited to this program'], 404);
        }

        // Check if application is in accepted or attend status
        if ($application->application_status !== 'accepted' && $application->application_status !== 'attend') {
            return response()->json(['message' => 'You must accept the invitation before marking attendance'], 400);
        }

        // Prepare response data
        $responseData = [
            'success' => true,
            'program' => [
                'program_id' => $program->program_id,
                'title' => $program->title,
                'date' => $program->date,
                'location' => $program->location,
            ],
            'student' => [
                'student_id' => $student->student_id,
                'name' => $student->applicant?->ar_name ?? $student->applicant?->en_name ?? 'N/A',
                'email' => $student->user?->email ?? 'N/A',
            ],
            'application' => [
                'application_id' => $application->application_program_id,
                'status' => $application->application_status,
                'marked_at' => $application->updated_at,
            ]
        ];

        // Check if attendance is already marked
        if ($application->application_status === 'attend') {
            $responseData['message'] = 'Attendance already marked';

            // Check if certificate token exists or should be generated
            if ($program->program_status === 'completed' && $program->generate_certificates) {
                if (!$application->certificate_token) {
                    // Generate certificate token for already marked attendance
                    $certificateToken = \Illuminate\Support\Str::random(32);
                    $application->update(['certificate_token' => $certificateToken]);
                    $responseData['certificate_token'] = $certificateToken;
                    $responseData['message'] = 'Attendance already marked. Certificate is now available.';
                } else {
                    // Certificate token already exists
                    $responseData['certificate_token'] = $application->certificate_token;
                    $responseData['message'] = 'Attendance already marked.';
                }
            }

            return response()->json($responseData);
        }

        try {
            // Use database transaction to prevent race conditions
            DB::beginTransaction();

            // Lock the application row to prevent concurrent updates
            $lockedApplication = ProgramApplication::where('student_id', $student->student_id)
                ->where('program_id', $program->program_id)
                ->lockForUpdate()
                ->first();

            // Double-check status after locking
            if ($lockedApplication->application_status === 'attend') {
                DB::rollBack();
                $responseData['message'] = 'Attendance already marked';
                $responseData['application']['status'] = $lockedApplication->application_status;
                $responseData['application']['marked_at'] = $lockedApplication->updated_at;
                return response()->json($responseData);
            }

            // Generate certificate token if program is completed and generate_certificates is enabled
            $certificateToken = null;
            if ($program->program_status === 'completed' && $program->generate_certificates) {
                $certificateToken = \Illuminate\Support\Str::random(32);
            }

            // Update status to attend and certificate token
            $updateData = ['application_status' => 'attend'];
            if ($certificateToken) {
                $updateData['certificate_token'] = $certificateToken;
            }

            $lockedApplication->update($updateData);

            DB::commit();

            $responseData['message'] = 'Attendance marked successfully! Welcome to the program.';
            $responseData['application']['status'] = 'attend';
            $responseData['application']['marked_at'] = $lockedApplication->fresh()->updated_at;

            // Add certificate token to response if generated
            if ($certificateToken) {
                $responseData['certificate_token'] = $certificateToken;
                $responseData['message'] = 'Attendance marked successfully! Certificate is now available.';
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to mark attendance',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all applications with accepted or attend status for a program
     */
    public function getProgramAttendance(Request $request, $programId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can view program attendance'], 403);
        }

        $program = Program::find($programId);
        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        // Get applications with accepted or attend status
        $applications = ProgramApplication::with(['student.user', 'student.applicant', 'student.approvedApplication.scholarship'])
            ->where('program_id', $programId)
            ->whereIn('application_status', ['accepted', 'attend'])
            ->whereHas('student')
            ->get();

        return response()->json([
            'program' => [
                'program_id' => $program->program_id,
                'title' => $program->title,
                'program_status' => $program->program_status,
                'date' => $program->date,
                'location' => $program->location,
            ],
            'applications' => $applications->map(function ($application) {
                return [
                    'application_id' => $application->application_program_id,
                    'student_id' => $application->student_id,
                    'name' => $application->student?->applicant?->ar_name ?? $application->student?->applicant?->en_name ?? 'N/A',
                    'email' => $application->student?->user?->email ?? 'N/A',
                    'university' => $application->student?->university ?? 'N/A',
                    'status' => $application->application_status,
                    'scholarship_name' => $application->student?->approvedApplication?->scholarship?->scholarship_name ?? 'N/A',
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at,
                ];
            }),
            'statistics' => [
                'total_accepted' => $applications->where('application_status', 'accepted')->count(),
                'total_attended' => $applications->where('application_status', 'attend')->count(),
                'total_eligible' => $applications->count(),
            ]
        ]);
    }

    /**
     * Admin: Update application status (accepted/attend) for multiple applications
     */
    public function updateApplicationStatus(Request $request, $programId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can update application status'], 403);
        }

        $data = $request->validate([
            'applications' => ['required', 'array', 'min:1'],
            'applications.*.application_id' => ['required', 'integer', 'exists:program_applications,application_program_id'],
            'applications.*.status' => ['required', 'string', 'in:accepted,attend'],
        ]);

        $program = Program::find($programId);
        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        try {
            DB::beginTransaction();

            $updatedApplications = [];
            $errors = [];

            foreach ($data['applications'] as $appData) {
                $application = ProgramApplication::with(['student.user', 'student.applicant'])
                    ->where('application_program_id', $appData['application_id'])
                    ->where('program_id', $programId)
                    ->first();

                if (!$application) {
                    $errors[] = [
                        'application_id' => $appData['application_id'],
                        'error' => 'Application not found for this program'
                    ];
                    continue;
                }

                // Validate status transition
                if ($application->application_status === 'invite' && $appData['status'] === 'attend') {
                    $errors[] = [
                        'application_id' => $appData['application_id'],
                        'error' => 'Cannot mark attendance without accepting invitation first'
                    ];
                    continue;
                }

                $application->update(['application_status' => $appData['status']]);

                $updatedApplications[] = [
                    'application_id' => $application->application_program_id,
                    'student_id' => $application->student_id,
                    'name' => $application->student?->applicant?->ar_name ?? $application->student?->applicant?->en_name ?? 'N/A',
                    'email' => $application->student?->user?->email ?? 'N/A',
                    'old_status' => $application->getOriginal('application_status'),
                    'new_status' => $appData['status'],
                    'updated_at' => $application->updated_at,
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Application statuses updated successfully',
                'updated_count' => count($updatedApplications),
                'error_count' => count($errors),
                'updated_applications' => $updatedApplications,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update application statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Public: Get certificate details by token (no authentication required)
     * Only accessible when program status is "completed"
     */
    public function getCertificate(Request $request, $token)
    {
        // Find application by certificate token
        $application = ProgramApplication::with(['student.user', 'student.applicant', 'program'])
            ->where('certificate_token', $token)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'Invalid certificate token'], 404);
        }

        // CRITICAL: Only allow access when program status is "completed"
        if ($application->program->program_status !== 'completed') {
            return response()->json([
                'message' => 'Certificate not yet available',
                'reason' => 'Program is not completed yet',
                'program_status' => $application->program->program_status,
                'available_when' => 'Program status becomes "completed"'
            ], 403);
        }

        // Check if certificates are enabled
        if (!$application->program->generate_certificates) {
            return response()->json(['message' => 'Certificate generation is disabled for this program'], 400);
        }

        // Check if application status is attend
        if ($application->application_status !== 'attend') {
            return response()->json(['message' => 'Certificate not available - attendance not marked'], 400);
        }

        return response()->json([
            'certificate' => [
                'application_id' => $application->application_program_id,
                'student_name' => $application->student->applicant?->ar_name ?? $application->student->applicant?->en_name ?? 'N/A',
                'program_title' => $application->program->title,
                'program_date' => $application->program->date,
                'attendance_date' => $application->updated_at,
                'program_location' => $application->program->location,
                'program_country' => $application->program->country,
                'certificate_token' => $application->certificate_token,
                'issued_at' => now(),
                'program_status' => $application->program->program_status,
            ]
        ]);
    }
}
