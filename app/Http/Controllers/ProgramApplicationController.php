<?php

namespace App\Http\Controllers;

use App\Models\ProgramApplication;
use App\Models\Program;
use App\Models\Student;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProgramApplicationController extends Controller
{
    /**
     * Admin: Invite student to program
     */
    public function inviteStudent(Request $request, $programId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can invite students'], 403);
        }

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,student_id'],
        ]);

        $program = Program::find($programId);
        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        $student = Student::find($data['student_id']);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        // Check if invitation already exists
        $existingApplication = ProgramApplication::where('student_id', $data['student_id'])
            ->where('program_id', $programId)
            ->first();

        if ($existingApplication) {
            return response()->json(['message' => 'Student already invited to this program'], 409);
        }

        try {
            $application = ProgramApplication::create([
                'student_id' => $data['student_id'],
                'program_id' => $programId,
                'application_status' => 'invite'
            ]);

            return response()->json([
                'message' => 'Student invited successfully',
                'application' => $application->load(['student.user', 'program'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to invite student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Invite multiple students to program
     */
    public function inviteMultipleStudents(Request $request, $programId)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can invite students'], 403);
        }

        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,student_id'],
        ]);

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
                    $alreadyInvited[] = $studentId;
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

            return response()->json([
                'message' => 'Invitations sent successfully',
                'invited_count' => count($invitedApplications),
                'already_invited_count' => count($alreadyInvited),
                'applications' => $invitedApplications,
                'already_invited_student_ids' => $alreadyInvited
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
        if (!$user || $user->role !== UserRole::STUDENT) {
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
        if (!$user || $user->role !== UserRole::STUDENT) {
            return response()->json(['message' => 'Only students can reject invitations'], 403);
        }

        $data = $request->validate([
            'excuse_reason' => ['required', 'string', 'max:1000'],
            'excuse_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'], // 5MB max
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
        if (!$user || $user->role !== UserRole::ADMIN) {
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
        if (!$user || $user->role !== UserRole::ADMIN) {
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
            $application->update(['application_status' => 'doesn_attend']);

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
        if (!$user || $user->role !== UserRole::STUDENT) {
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
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view program applications'], 403);
        }

        $program = Program::find($programId);
        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        $applications = ProgramApplication::with(['student.user', 'program'])
            ->where('program_id', $programId)
            ->get();

        return response()->json([
            'program' => $program,
            'applications' => $applications
        ]);
    }

    /**
     * Student: Get my program applications
     */
    public function getMyApplications(Request $request)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role !== UserRole::STUDENT) {
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
     * Student: QR Code attendance with token (requires student authentication)
     */
    public function qrAttendanceWithToken(Request $request, $token)
    {
        $user = $request->user();

        // Check if user is student
        if (!$user || $user->role !== UserRole::STUDENT) {
            return response()->json(['message' => 'Only students can mark attendance'], 403);
        }

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,student_id'],
        ]);

        // Find program by QR token
        $program = Program::where('qr_url', 'like', "%{$token}")->first();

        if (!$program) {
            return response()->json(['message' => 'Invalid QR code'], 404);
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
}
