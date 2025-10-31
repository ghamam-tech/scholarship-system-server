<?php

namespace App\Http\Controllers;

use App\Models\ApplicationOpportunity;
use App\Models\Opportunity;
use App\Models\Student;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ApplicationOpportunityController extends Controller
{

    /**
     * Normalize an application identifier into a loaded ApplicationOpportunity model.
     */
    private function resolveApplication($applicationId)
    {
        // Collection → first model
        if ($applicationId instanceof \Illuminate\Support\Collection) {
            $applicationId = $applicationId->first();
        }

        // Model instance → return with relations
        if ($applicationId instanceof ApplicationOpportunity) {
            return ApplicationOpportunity::with(['student.user', 'student.applicant', 'opportunity'])
                ->find($applicationId->application_opportunity_id);
        }

        // Formatted ID like opp_0000008
        if (is_string($applicationId) && preg_match('/^opp_(\d+)$/', $applicationId, $m)) {
            $applicationId = (int) $m[1];
        }

        // Fallback: numeric id
        if (is_numeric($applicationId)) {
            return ApplicationOpportunity::with(['student.user', 'student.applicant', 'opportunity'])
                ->find((int) $applicationId);
        }

        return null;
    }

    /**
     * Normalize an opportunity identifier into a numeric ID.
     */
    private function normalizeOpportunityId($opportunityId)
    {
        // Collection → first model → ID
        if ($opportunityId instanceof \Illuminate\Support\Collection) {
            $first = $opportunityId->first();
            if ($first instanceof Opportunity) {
                return $first->opportunity_id;
            }
            return $first;
        }

        // Model instance → ID
        if ($opportunityId instanceof Opportunity) {
            return $opportunityId->opportunity_id;
        }

        // Already numeric
        return $opportunityId;
    }

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
     * Admin: Invite multiple students to opportunity
     */
    public function inviteMultipleStudents(Request $request, $opportunityId)
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

        // Normalize opportunity ID
        $opportunityId = $this->normalizeOpportunityId($opportunityId);

        $opportunity = Opportunity::where('opportunity_id', $opportunityId)->first();
        Log::info('Opportunity lookup result', [
            'found' => (bool) $opportunity,
            'resolved_id' => $opportunity?->opportunity_id,
            'title' => $opportunity?->title,
        ]);
        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        try {
            DB::beginTransaction();

            $invitedApplications = [];
            $alreadyInvited = [];

            foreach ($data['student_ids'] as $studentId) {
                // Check if invitation already exists
                $existingApplication = ApplicationOpportunity::where('student_id', $studentId)
                    ->where('opportunity_id', $opportunity->opportunity_id)
                    ->first();

                if ($existingApplication) {
                    $alreadyInvited[] = $existingApplication->load(['student.user', 'student.applicant']);
                    continue;
                }

                $application = ApplicationOpportunity::create([
                    'student_id' => $studentId,
                    'opportunity_id' => $opportunity->opportunity_id,
                    'application_status' => 'invite'
                ]);

                $invitedApplications[] = $application->load(['student.user', 'opportunity']);
            }

            DB::commit();

            // Get all existing applications for this opportunity
            $allExistingApplications = ApplicationOpportunity::with(['student.user', 'student.applicant'])
                ->where('opportunity_id', $opportunity->opportunity_id)
                ->get();

            return response()->json([
                'message' => 'Invitations sent successfully',
                'invited_count' => count($invitedApplications),
                'already_invited_count' => count($alreadyInvited),
                'applications' => collect($invitedApplications)->map(function ($application) {
                    return [
                        'application_opportunity_id' => $application->formatted_id,
                        'student_id' => $application->student_id,
                        'ar_name' => $application->student?->applicant?->ar_name ?? 'N/A',
                        'email' => $application->student?->user?->email ?? 'N/A',
                        'status' => $application->application_status,
                    ];
                }),
                'already_invited_student_ids' => collect($alreadyInvited)->map(function ($application) {
                    return [
                        'application_opportunity_id' => $application->formatted_id,
                        'student_id' => $application->student_id,
                        'ar_name' => $application->student?->applicant?->ar_name ?? 'N/A',
                        'email' => $application->student?->user?->email ?? 'N/A',
                        'status' => $application->application_status,
                    ];
                }),
                'all_opportunity_applications' => $allExistingApplications->map(function ($application) {
                    return [
                        'application_opportunity_id' => $application->formatted_id,
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

        // Normalize ID: accept collection, model instance, and formatted IDs like opp_0000008
        if ($applicationId instanceof \Illuminate\Support\Collection) {
            $first = $applicationId->first();
            $applicationId = $first?->application_opportunity_id;
        } elseif ($applicationId instanceof ApplicationOpportunity) {
            $applicationId = $applicationId->application_opportunity_id;
        } elseif (is_string($applicationId) && preg_match('/^opp_(\\d+)$/', $applicationId, $m)) {
            $applicationId = $m[1];
        }

        // Normalize ID: accept collection, model instance, and formatted IDs like opp_0000008
        if ($applicationId instanceof \Illuminate\Support\Collection) {
            $first = $applicationId->first();
            $applicationId = $first?->application_opportunity_id;
        } elseif ($applicationId instanceof ApplicationOpportunity) {
            $applicationId = $applicationId->application_opportunity_id;
        } elseif (is_string($applicationId) && preg_match('/^opp_(\\d+)$/', $applicationId, $m)) {
            $applicationId = $m[1];
        }

        $application = $this->resolveApplication($applicationId);

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
                'application' => [
                    'application_opportunity_id' => $application->formatted_id,
                    'application_status' => $application->application_status,
                    'certificate_token' => $application->certificate_token,
                    'comment' => $application->comment,
                    'excuse_reason' => $application->excuse_reason,
                    'excuse_file' => $application->excuse_file,
                    'attendece_mark' => $application->attendece_mark,
                    'student_id' => $application->student_id,
                    'opportunity_id' => $application->opportunity_id,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at
                ]
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

        // Normalize ID: accept model instance and formatted IDs like opp_0000008
        if ($applicationId instanceof ApplicationOpportunity) {
            $applicationId = $applicationId->application_opportunity_id;
        } elseif (is_string($applicationId) && preg_match('/^opp_(\\d+)$/', $applicationId, $m)) {
            $applicationId = $m[1];
        }

        $application = $this->resolveApplication($applicationId);

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
                $excusePath = $excuseFile->store('opportunity_applications/excuses', 'public');
                $updateData['excuse_file'] = $excusePath;
            }

            $application->update($updateData);

            DB::commit();

            return response()->json([
                'message' => 'Invitation rejected with excuse',
                'application' => [
                    'application_opportunity_id' => $application->formatted_id,
                    'application_status' => $application->application_status,
                    'certificate_token' => $application->certificate_token,
                    'comment' => $application->comment,
                    'excuse_reason' => $application->excuse_reason,
                    'excuse_file' => $application->excuse_file,
                    'attendece_mark' => $application->attendece_mark,
                    'student_id' => $application->student_id,
                    'opportunity_id' => $application->opportunity_id,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at
                ]
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

        $application = $this->resolveApplication($applicationId);
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
                'application' => [
                    'application_opportunity_id' => $application->formatted_id,
                    'application_status' => $application->application_status,
                    'certificate_token' => $application->certificate_token,
                    'comment' => $application->comment,
                    'excuse_reason' => $application->excuse_reason,
                    'excuse_file' => $application->excuse_file,
                    'attendece_mark' => $application->attendece_mark,
                    'student_id' => $application->student_id,
                    'opportunity_id' => $application->opportunity_id,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at
                ]
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

        $application = $this->resolveApplication($applicationId);
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
                'application' => [
                    'application_opportunity_id' => $application->formatted_id,
                    'application_status' => $application->application_status,
                    'certificate_token' => $application->certificate_token,
                    'comment' => $application->comment,
                    'excuse_reason' => $application->excuse_reason,
                    'excuse_file' => $application->excuse_file,
                    'attendece_mark' => $application->attendece_mark,
                    'student_id' => $application->student_id,
                    'opportunity_id' => $application->opportunity_id,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at
                ]
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

        $application = $this->resolveApplication($applicationId);

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
            // Generate certificate token if opportunity is completed and generate_certificates is enabled
            $certificateToken = null;
            if ($application->opportunity->opportunity_status === 'completed' && $application->opportunity->generate_certificates) {
                $certificateToken = \Illuminate\Support\Str::random(32);
            }

            // Update status to attend and certificate token
            $updateData = ['application_status' => 'attend'];
            if ($certificateToken) {
                $updateData['certificate_token'] = $certificateToken;
            }

            $application->update($updateData);

            $responseData = [
                'message' => 'Attendance marked successfully',
                'application' => [
                    'application_opportunity_id' => $application->formatted_id,
                    'application_status' => $application->application_status,
                    'certificate_token' => $application->certificate_token,
                    'comment' => $application->comment,
                    'excuse_reason' => $application->excuse_reason,
                    'excuse_file' => $application->excuse_file,
                    'attendece_mark' => $application->attendece_mark,
                    'student_id' => $application->student_id,
                    'opportunity_id' => $application->opportunity_id,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at
                ]
            ];

            // Add certificate token to response if generated
            if ($certificateToken) {
                $responseData['certificate_token'] = $certificateToken;
                $responseData['message'] = 'Attendance marked successfully! Certificate is now available.';
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all opportunity applications
     */
    public function getOpportunityApplications(Request $request, $opportunityId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can view opportunity applications'], 403);
        }

        // Normalize opportunity ID
        $opportunityId = $this->normalizeOpportunityId($opportunityId);

        // Debug logging: verify environment and DB for this read path
        Log::info('GetOpportunityApplications request', [
            'opportunity_param' => $opportunityId,
            'app_env' => config('app.env'),
            'default_db_connection' => config('database.default'),
            'db_name' => DB::connection()->getDatabaseName(),
            'db_host' => config('database.connections.' . config('database.default') . '.host'),
            'db_database' => config('database.connections.' . config('database.default') . '.database'),
        ]);

        $opportunity = Opportunity::find($opportunityId);
        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        $applications = ApplicationOpportunity::with(['student.user', 'student.applicant', 'student.approvedApplication.scholarship', 'student.approvedApplication.application'])
            ->where('opportunity_id', $opportunityId)
            ->whereHas('student') // Only get applications with valid students
            ->get();

        // Guard against any accidental collection shadowing and fetch title explicitly
        $opportunityTitle = Opportunity::where('opportunity_id', $opportunityId)->value('title');

        return response()->json([
            'opportunity' => [
                'opportunity_id' => $opportunityId,
                'title' => $opportunityTitle
            ],
            'applications' => $applications->map(function ($application) {
                return [
                    'application_id' => $application->formatted_id,
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
     * Admin: Delete opportunity application
     */
    public function deleteApplication(Request $request, $applicationId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can delete applications'], 403);
        }

        // Accept both numeric IDs and formatted IDs like "opp_0000038"
        Log::info('DeleteOpportunityApplication request', [
            'raw_id' => $applicationId,
        ]);
        $numericId = $applicationId;
        if ($applicationId instanceof ApplicationOpportunity) {
            $numericId = $applicationId->application_opportunity_id;
        } elseif (is_string($applicationId) && preg_match('/^opp_(\d+)$/', $applicationId, $matches)) {
            $numericId = $matches[1];
        }
        Log::info('Parsed application id', ['numeric_id' => $numericId]);

        $application = ApplicationOpportunity::with(['student.user', 'student.applicant', 'opportunity'])
            ->where('application_opportunity_id', $numericId)
            ->first();

        if (!$application) {
            Log::info('DeleteOpportunityApplication not found', ['numeric_id' => $numericId]);
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
                    'application_opportunity_id' => $application->formatted_id,
                    'student_id' => $application->student_id,
                    'ar_name' => $application->student->applicant->ar_name ?? 'N/A',
                    'email' => $application->student->user->email,
                    'opportunity_title' => $application->opportunity->title,
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

        // Normalize ID: support model instance and formatted IDs like opp_0000008
        if ($applicationId instanceof ApplicationOpportunity) {
            $applicationId = $applicationId->application_opportunity_id;
        } elseif (is_string($applicationId) && preg_match('/^opp_(\\d+)$/', $applicationId, $m)) {
            $applicationId = $m[1];
        }

        $application = ApplicationOpportunity::with(['student.user', 'student.applicant', 'opportunity'])
            ->find($applicationId);
        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        // Check if application has or had an excuse (pending/approved/rejected)
        if (!in_array($application->application_status, ['excuse', 'approved_excuse', 'rejected_excuse'])) {
            return response()->json(['message' => 'Application does not have an excuse'], 400);
        }

        return response()->json([
            'application' => [
                'application_id' => $application->formatted_id,
                'excuse_reason' => $application->excuse_reason,
                'excuse_file' => $application->excuse_file,
                'excuse_file_url' => $application->excuse_file ? asset('storage/' . $application->excuse_file) : null,
                'email' => $application->student->user->email,
                'ar_name' => $application->student->applicant->ar_name,
                'status' => $application->application_status,
                'opportunity_title' => $application->opportunity->title,
                'program_title' => $application->opportunity->title,
                'submitted_at' => $application->updated_at
            ]
        ]);
    }

    /**
     * Student: Get my opportunity applications
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

        $applications = ApplicationOpportunity::with(['opportunity'])
            ->where('student_id', $student->student_id)
            ->get();

        return response()->json([
            'applications' => $applications->map(function ($application) {
                return [
                    'application_opportunity_id' => $application->formatted_id,
                    'application_status' => $application->application_status,
                    'certificate_token' => $application->certificate_token,
                    'comment' => $application->comment,
                    'excuse_reason' => $application->excuse_reason,
                    'excuse_file' => $application->excuse_file,
                    'attendece_mark' => $application->attendece_mark,
                    'student_id' => $application->student_id,
                    'opportunity_id' => $application->opportunity_id,
                    'created_at' => $application->created_at,
                    'updated_at' => $application->updated_at,
                    'opportunity' => $application->opportunity
                ];
            })
        ]);
    }

    /**
     * Student: Get all opportunities that the student has applications for
     */
    public function getOpportunitiesForStudent(Request $request)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can view their opportunities'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $applications = ApplicationOpportunity::with(['opportunity'])
            ->where('student_id', $student->student_id)
            ->get();

        $opportunities = $applications->map(function ($application) {
            // Check if opportunity relationship exists and is a model (not collection)
            if (!$application->opportunity || is_a($application->opportunity, 'Illuminate\Database\Eloquent\Collection')) {
                return null; // Skip this application if opportunity is null or collection
            }

            // Get enrollment count for this opportunity
            $enrollmentCount = ApplicationOpportunity::where('opportunity_id', $application->opportunity->opportunity_id)
                ->where('application_status', 'accepted')
                ->count();

            // Get total applications for this opportunity
            $totalApplications = ApplicationOpportunity::where('opportunity_id', $application->opportunity->opportunity_id)->count();

            return [
                'opportunity_id' => $application->opportunity->opportunity_id,
                'title' => $application->opportunity->title,
                'description' => $application->opportunity->discription,
                'date' => $application->opportunity->date,
                'location' => $application->opportunity->location,
                'country' => $application->opportunity->country,
                'category' => $application->opportunity->category,
                'opportunity_status' => $application->opportunity->opportunity_status,
                'start_date' => $application->opportunity->start_date,
                'end_date' => $application->opportunity->end_date,
                'volunteer_role' => $application->opportunity->volunteer_role,
                'volunteering_hours' => $application->opportunity->volunteering_hours,

                // Opportunity image and QR
                'image_file' => $application->opportunity->image_file,
                'image_url' => $application->opportunity->image_file ? asset('storage/' . $application->opportunity->image_file) : null,

                'enrollment_text' => $enrollmentCount . ' enrolled',

                // Application details
                'application_status' => $application->application_status,
                'application_id' => $application->formatted_id,
            ];
        });

        // Filter out null values
        $opportunities = $opportunities->filter()->values();

        return response()->json([
            'student' => [
                'student_id' => $student->student_id,
                'name' => $student->applicant?->ar_name ?? $student->applicant?->en_name ?? 'N/A',
                'email' => $user->email,
            ],
            'opportunities' => $opportunities,
            'total_opportunities' => $opportunities->count()
        ]);
    }

    /**
     * Get opportunity by ID
     */
    public function getOpportunityById(Request $request, $opportunityId)
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        // Normalize opportunity ID
        $opportunityId = $this->normalizeOpportunityId($opportunityId);

        $opportunity = Opportunity::find($opportunityId);
        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        // Get enrollment count for this opportunity
        $enrollmentCount = ApplicationOpportunity::where('opportunity_id', $opportunity->opportunity_id)
            ->where('application_status', 'accepted')
            ->count();

        // Get total applications for this opportunity
        $totalApplications = ApplicationOpportunity::where('opportunity_id', $opportunity->opportunity_id)->count();

        // Get opportunity coordinator details
        $coordinator = [
            'name' => $opportunity->opportunity_coordinatior_name,
            'phone' => $opportunity->opportunity_coordinatior_phone,
            'email' => $opportunity->opportunity_coordinatior_email,
        ];

        return response()->json([
            'opportunity' => [
                'opportunity_id' => $opportunity->opportunity_id,
                'title' => $opportunity->title,
                'description' => $opportunity->discription,
                'date' => $opportunity->date,
                'location' => $opportunity->location,
                'country' => $opportunity->country,
                'category' => $opportunity->category,
                'opportunity_status' => $opportunity->opportunity_status,
                'start_date' => $opportunity->start_date,
                'end_date' => $opportunity->end_date,
                'volunteer_role' => $opportunity->volunteer_role,
                'volunteering_hours' => $opportunity->volunteering_hours,
                'enable_qr_attendance' => $opportunity->enable_qr_attendance,
                'generate_certificates' => $opportunity->generate_certificates,

                // Opportunity coordinator details
                'coordinator' => $coordinator,

                // Opportunity image and QR
                'image_file' => $opportunity->image_file,
                'image_url' => $opportunity->image_file ? asset('storage/' . $opportunity->image_file) : null,
                'qr_url' => $opportunity->qr_url,

                // Enrollment and application statistics
                'enrollment_count' => $enrollmentCount,
                'total_applications' => $totalApplications,
                'enrollment_text' => $enrollmentCount . ' enrolled',

                // Timestamps
                'created_at' => $opportunity->created_at,
                'updated_at' => $opportunity->updated_at,
            ]
        ]);
    }

    /**
     * Get student's opportunity application by Opportunity ID
     */
    public function getMyOpportunityApplication(Request $request, $opportunityId)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can view their opportunity applications'], 403);
        }

        // Normalize opportunity ID
        $opportunityId = $this->normalizeOpportunityId($opportunityId);

        // Find the student record for this user
        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Find the application for this student and opportunity
        $application = ApplicationOpportunity::with(['student.user', 'student.applicant', 'opportunity', 'student.approvedApplication.scholarship'])
            ->where('opportunity_id', $opportunityId)
            ->where('student_id', $student->student_id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'No application found for this opportunity'], 404);
        }

        return response()->json([
            'application' => [
                'application_id' => $application->formatted_id,
                'student_id' => $application->student_id,
                'opportunity_id' => $application->opportunity_id,
                'application_status' => $application->application_status,
                'excuse_reason' => $application->excuse_reason,
                'excuse_file' => $application->excuse_file,
                'excuse_file_url' => $application->excuse_file ? asset('storage/' . $application->excuse_file) : null,
                'certificate_token' => $application->certificate_token,
                'comment' => $application->comment,
                'attendece_mark' => $application->attendece_mark,
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

                // Opportunity details
                'opportunity' => [
                    'opportunity_id' => $application->opportunity->opportunity_id,
                    'title' => $application->opportunity->title,
                    'description' => $application->opportunity->discription,
                    'date' => $application->opportunity->date,
                    'location' => $application->opportunity->location,
                    'country' => $application->opportunity->country,
                    'category' => $application->opportunity->category,
                    'opportunity_status' => $application->opportunity->opportunity_status,
                    'start_date' => $application->opportunity->start_date,
                    'end_date' => $application->opportunity->end_date,
                    'volunteer_role' => $application->opportunity->volunteer_role,
                    'volunteering_hours' => $application->opportunity->volunteering_hours,
                    'enable_qr_attendance' => $application->opportunity->enable_qr_attendance,
                    'generate_certificates' => $application->opportunity->generate_certificates,
                    'coordinator_name' => $application->opportunity->opportunity_coordinatior_name,
                    'coordinator_phone' => $application->opportunity->opportunity_coordinatior_phone,
                    'coordinator_email' => $application->opportunity->opportunity_coordinatior_email,
                    'image_file' => $application->opportunity->image_file,
                    'image_url' => $application->opportunity->image_file ? asset('storage/' . $application->opportunity->image_file) : null,
                    'qr_url' => $application->opportunity->qr_url,
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

        // Find opportunity by QR token
        $opportunity = Opportunity::where('qr_url', $token)->first();

        if (!$opportunity) {
            return response()->json(['message' => 'Invalid QR code'], 404);
        }

        // Check if opportunity is active
        if ($opportunity->opportunity_status !== 'active') {
            return response()->json([
                'message' => 'QR attendance is not available',
                'reason' => 'Opportunity is not active',
                'opportunity_status' => $opportunity->opportunity_status,
                'available_when' => 'Opportunity status is "active"'
            ], 403);
        }

        // Check if QR attendance is enabled
        if (!$opportunity->enable_qr_attendance) {
            return response()->json(['message' => 'QR attendance is not enabled for this opportunity'], 400);
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

        // Find application for this student and opportunity
        $application = ApplicationOpportunity::where('student_id', $student->student_id)
            ->where('opportunity_id', $opportunity->opportunity_id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'No invitation found for this opportunity'], 404);
        }

        // Check if application is in accepted status
        if ($application->application_status !== 'accepted') {
            return response()->json(['message' => 'Application must be accepted before marking attendance'], 400);
        }

        try {
            // Generate certificate token if opportunity is completed and generate_certificates is enabled
            $certificateToken = null;
            if ($application->opportunity->opportunity_status === 'completed' && $application->opportunity->generate_certificates) {
                $certificateToken = \Illuminate\Support\Str::random(32);
            }

            // Update status to attend and certificate token
            $updateData = ['application_status' => 'attend'];
            if ($certificateToken) {
                $updateData['certificate_token'] = $certificateToken;
            }

            $application->update($updateData);

            $responseData = [
                'message' => 'Attendance marked successfully',
                'application' => $application->load(['student.user', 'opportunity']),
                'student' => [
                    'student_id' => $student->student_id,
                    'name' => $student->en_name ?? $student->ar_name,
                    'email' => $user->email
                ]
            ];

            // Add certificate token to response if generated
            if ($certificateToken) {
                $responseData['certificate_token'] = $certificateToken;
                $responseData['message'] = 'Attendance marked successfully! Certificate is now available.';
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Student: Mark attendance via QR token (requires student authentication)
     * Only students invited to the opportunity can mark attendance
     * Only works when opportunity status is "active"
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

        // Find opportunity by QR token
        $opportunity = Opportunity::where('qr_url', $token)->first();

        if (!$opportunity) {
            return response()->json(['message' => 'Invalid QR code'], 404);
        }

        // Check if opportunity is active
        if ($opportunity->opportunity_status !== 'active') {
            return response()->json([
                'message' => 'QR attendance is not available',
                'reason' => 'Opportunity is not active',
                'opportunity_status' => $opportunity->opportunity_status,
                'available_when' => 'Opportunity status is "active"'
            ], 403);
        }

        // Check if QR attendance is enabled
        if (!$opportunity->enable_qr_attendance) {
            return response()->json(['message' => 'QR attendance is not enabled for this opportunity'], 400);
        }

        // Find application for this student and opportunity
        $application = ApplicationOpportunity::where('student_id', $student->student_id)
            ->where('opportunity_id', $opportunity->opportunity_id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'You are not invited to this opportunity'], 404);
        }

        // Check if application is in accepted or attend status
        if ($application->application_status !== 'accepted' && $application->application_status !== 'attend') {
            return response()->json(['message' => 'You must accept the invitation before marking attendance'], 400);
        }

        // Prepare response data
        $responseData = [
            'success' => true,
            'opportunity' => [
                'opportunity_id' => $opportunity->opportunity_id,
                'title' => $opportunity->title,
                'date' => $opportunity->date,
                'location' => $opportunity->location,
                'volunteer_role' => $opportunity->volunteer_role,
            ],
            'student' => [
                'student_id' => $student->student_id,
                'name' => $student->applicant?->ar_name ?? $student->applicant?->en_name ?? 'N/A',
                'email' => $student->user?->email ?? 'N/A',
            ],
            'application' => [
                'application_id' => $application->formatted_id,
                'status' => $application->application_status,
                'marked_at' => $application->updated_at,
            ]
        ];

        // Check if attendance is already marked
        if ($application->application_status === 'attend') {
            $responseData['message'] = 'Attendance already marked';

            // Check if certificate token exists or should be generated
            if ($opportunity->opportunity_status === 'completed' && $opportunity->generate_certificates) {
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
            $lockedApplication = ApplicationOpportunity::where('student_id', $student->student_id)
                ->where('opportunity_id', $opportunity->opportunity_id)
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

            // Generate certificate token if opportunity is completed and generate_certificates is enabled
            $certificateToken = null;
            if ($opportunity->opportunity_status === 'completed' && $opportunity->generate_certificates) {
                $certificateToken = \Illuminate\Support\Str::random(32);
            }

            // Update status to attend and certificate token
            $updateData = ['application_status' => 'attend'];
            if ($certificateToken) {
                $updateData['certificate_token'] = $certificateToken;
            }

            $lockedApplication->update($updateData);

            DB::commit();

            $responseData['message'] = 'Attendance marked successfully! Welcome to the opportunity.';
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
     * Admin: Get all applications with accepted or attend status for an opportunity
     */
    public function getOpportunityAttendance(Request $request, $opportunityId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can view opportunity attendance'], 403);
        }

        // Normalize opportunity ID
        $opportunityId = $this->normalizeOpportunityId($opportunityId);

        $opportunity = Opportunity::find($opportunityId);
        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        // Get applications with accepted or attend status
        $applications = ApplicationOpportunity::with(['student.user', 'student.applicant', 'student.approvedApplication.scholarship'])
            ->where('opportunity_id', $opportunity->opportunity_id)
            ->whereIn('application_status', ['accepted', 'attend'])
            ->whereHas('student')
            ->get();

        return response()->json([
            'opportunity' => [
                'opportunity_id' => $opportunity->opportunity_id,
                'title' => $opportunity->title,
                'opportunity_status' => $opportunity->opportunity_status,
                'date' => $opportunity->date,
                'location' => $opportunity->location,
                'volunteer_role' => $opportunity->volunteer_role,
            ],
            'applications' => $applications->map(function ($application) {
                return [
                    'application_id' => $application->formatted_id,
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
    public function updateApplicationStatus(Request $request, $opportunityId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can update application status'], 403);
        }

        $data = $request->validate([
            'applications' => ['required', 'array', 'min:1'],
            'applications.*.application_id' => ['required', 'string', function ($attribute, $value, $fail) {
                // Handle both formatted IDs (opp_0000038) and raw IDs (38)
                $numericId = $value;
                if (preg_match('/^opp_(\d+)$/', $value, $matches)) {
                    $numericId = $matches[1];
                }

                if (!ApplicationOpportunity::where('application_opportunity_id', $numericId)->exists()) {
                    $fail('The selected ' . $attribute . ' is invalid.');
                }
            }],
            'applications.*.status' => ['required', 'string', 'in:accepted,attend'],
        ]);

        // Normalize opportunity ID
        $opportunityId = $this->normalizeOpportunityId($opportunityId);

        $opportunity = Opportunity::find($opportunityId);
        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        try {
            DB::beginTransaction();

            $updatedApplications = [];
            $errors = [];

            foreach ($data['applications'] as $appData) {
                // Extract numeric ID from formatted ID if needed
                $numericId = $appData['application_id'];
                if (preg_match('/^opp_(\d+)$/', $appData['application_id'], $matches)) {
                    $numericId = $matches[1];
                }

                $application = ApplicationOpportunity::with(['student.user', 'student.applicant'])
                    ->where('application_opportunity_id', $numericId)
                    ->where('opportunity_id', $opportunity->opportunity_id)
                    ->first();

                if (!$application) {
                    $errors[] = [
                        'application_id' => $appData['application_id'],
                        'error' => 'Application not found for this opportunity'
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

                // Prepare update data
                $updateData = ['application_status' => $appData['status']];

                // Generate certificate token if status is being set to 'attend'
                if ($appData['status'] === 'attend') {
                    // Check if opportunity is completed and certificate generation is enabled
                    if ($opportunity->opportunity_status === 'completed' && $opportunity->generate_certificates) {
                        // Only generate token if one doesn't already exist
                        if (!$application->certificate_token) {
                            $updateData['certificate_token'] = \Illuminate\Support\Str::random(32);
                        }
                    }
                }

                $application->update($updateData);

                $applicationData = [
                    'application_id' => $application->formatted_id,
                    'student_id' => $application->student_id,
                    'name' => $application->student?->applicant?->ar_name ?? $application->student?->applicant?->en_name ?? 'N/A',
                    'email' => $application->student?->user?->email ?? 'N/A',
                    'old_status' => $application->getOriginal('application_status'),
                    'new_status' => $appData['status'],
                    'updated_at' => $application->updated_at,
                ];

                // Include certificate token if it was generated
                if ($appData['status'] === 'attend' && isset($updateData['certificate_token'])) {
                    $applicationData['certificate_token'] = $updateData['certificate_token'];
                    $applicationData['certificate_generated'] = true;
                }

                $updatedApplications[] = $applicationData;
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
     * Admin: Generate certificate tokens for existing attendance records
     * This method can be used to fix existing records that should have certificate tokens
     */
    public function generateMissingCertificateTokens(Request $request, $opportunityId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can generate certificate tokens'], 403);
        }

        // Normalize opportunity ID
        $opportunityId = $this->normalizeOpportunityId($opportunityId);

        $opportunity = Opportunity::find($opportunityId);
        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        // Check if opportunity is completed and certificate generation is enabled
        if ($opportunity->opportunity_status !== 'completed') {
            return response()->json(['message' => 'Opportunity must be completed to generate certificates'], 400);
        }

        if (!$opportunity->generate_certificates) {
            return response()->json(['message' => 'Certificate generation is disabled for this opportunity'], 400);
        }

        try {
            // Find all attendance records without certificate tokens
            $applications = ApplicationOpportunity::where('opportunity_id', $opportunityId)
                ->where('application_status', 'attend')
                ->whereNull('certificate_token')
                ->get();

            $updatedCount = 0;
            $updatedApplications = [];

            foreach ($applications as $application) {
                $certificateToken = \Illuminate\Support\Str::random(32);
                $application->update(['certificate_token' => $certificateToken]);

                $updatedCount++;
                $updatedApplications[] = [
                    'application_id' => $application->formatted_id,
                    'student_id' => $application->student_id,
                    'certificate_token' => $certificateToken,
                    'updated_at' => $application->updated_at
                ];
            }

            return response()->json([
                'message' => 'Certificate tokens generated successfully',
                'opportunity_id' => $opportunityId,
                'opportunity_title' => $opportunity->title,
                'updated_count' => $updatedCount,
                'updated_applications' => $updatedApplications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate certificate tokens',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Public: Get certificate details by token (no authentication required)
     * Only accessible when opportunity status is "completed"
     */
    public function getCertificate(Request $request, $token)
    {
        // Find application by certificate token
        $application = ApplicationOpportunity::with(['student.user', 'student.applicant', 'opportunity'])
            ->where('certificate_token', $token)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'Invalid certificate token'], 404);
        }

        // CRITICAL: Only allow access when opportunity status is "completed"
        if ($application->opportunity->opportunity_status !== 'completed') {
            return response()->json([
                'message' => 'Certificate not yet available',
                'reason' => 'Opportunity is not completed yet',
                'opportunity_status' => $application->opportunity->opportunity_status,
                'available_when' => 'Opportunity status becomes "completed"'
            ], 403);
        }

        // Check if certificates are enabled
        if (!$application->opportunity->generate_certificates) {
            return response()->json(['message' => 'Certificate generation is disabled for this opportunity'], 400);
        }

        // Check if application status is attend
        if ($application->application_status !== 'attend') {
            return response()->json(['message' => 'Certificate not available - attendance not marked'], 400);
        }

        return response()->json([
            'certificate' => [
                'application_id' => $application->formatted_id,
                'student_name' => $application->student->applicant?->ar_name ?? $application->student->applicant?->en_name ?? 'N/A',
                'opportunity_title' => $application->opportunity->title,
                'opportunity_date' => $application->opportunity->date,
                'attendance_date' => $application->updated_at,
                'opportunity_location' => $application->opportunity->location,
                'opportunity_country' => $application->opportunity->country,
                'volunteer_role' => $application->opportunity->volunteer_role,
                'volunteering_hours' => $application->opportunity->volunteering_hours,
                'certificate_token' => $application->certificate_token,
                'issued_at' => now(),
                'opportunity_status' => $application->opportunity->opportunity_status,
            ]
        ]);
    }
}
