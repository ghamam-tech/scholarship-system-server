<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Applicant;
use App\Models\UserStatus;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
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
}

