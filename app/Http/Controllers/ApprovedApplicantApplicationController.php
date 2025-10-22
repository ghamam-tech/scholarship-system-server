<?php

namespace App\Http\Controllers;

use App\Models\ApprovedApplicantApplication;
use App\Models\Applicant;
use App\Models\ApplicantApplication;
use App\Models\ApplicantApplicationStatus;
use App\Models\Student;
use App\Models\User;
use App\Models\Scholarship;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovedApplicantApplicationController extends Controller
{
    /**
     * Admin-only: finalize approval for an application and promote to Student.
     *
     * Changes per your notes:
     *  - Application belongs to USER (sanity check on application.user_id).
     *  - Application PK is `application_id`.
     *  - `has_accepted_scholarship` is forced to false on creation.
     */
    public function store(Request $request)
    {
        // 1) Admin gate
        $authUser = $request->user();
        $roleVal = is_object($authUser->role) ? $authUser->role->value : (string) $authUser->role;
        if ($roleVal !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can approve applications'], 403);
        }

        // 2) Validate payload (no has_accepted_scholarship here; we force false)
        $data = $request->validate([
            'benefits' => ['nullable'], // array|object|string; see cast note below
            'scholarship_id' => ['required', 'exists:scholarships,scholarship_id'],
            'application_id' => ['required', 'exists:applicant_applications,application_id'],
            'user_id' => ['required', 'exists:users,user_id'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            return DB::transaction(function () use ($data) {
                // Pull core records
                /** @var User $targetUser */
                $targetUser = User::where('user_id', $data['user_id'])->firstOrFail();

                /** @var ApplicantApplication $application */
                $application = ApplicantApplication::with('applicant')
                    ->where('application_id', $data['application_id'])
                    ->firstOrFail();

                /** @var Scholarship $scholarship */
                $scholarship = Scholarship::where('scholarship_id', $data['scholarship_id'])->firstOrFail();

                // ---- Integrity checks ----

                // A) Application must belong to provided user via Applicant
                $applicant = $application->applicant;
                if (!$applicant) {
                    return response()->json(['message' => 'Applicant not found for this application'], 422);
                }
                if ((int) $applicant->user_id !== (int) $targetUser->user_id) {
                    return response()->json(['message' => 'Provided user does not own this application'], 422);
                }

                // (Optional) Enforce that the application’s scholarship matches the provided scholarship
                // if (
                //     isset($application->scholarship_id) &&
                //     (int) $application->scholarship_id !== (int) $scholarship->scholarship_id
                // ) {
                //     return response()->json(['message' => 'Scholarship does not match the application'], 422);
                // }

                // B) Exactly one approval per application
                // $alreadyApproved = ApprovedApplicantApplication::where('application_id', $application->application_id)->exists();
                // if ($alreadyApproved) {
                //     return response()->json(['message' => 'This application already has a final approval record'], 409);
                // }

                // Normalize benefits if array/object → JSON string (safe even if string)
                $benefitsValue = $data['benefits'] ?? null;
                if (is_array($benefitsValue) || is_object($benefitsValue)) {
                    $benefitsValue = json_encode($benefitsValue, JSON_UNESCAPED_UNICODE);
                }

                // 3) Create ApprovedApplicantApplication (force has_accepted_scholarship = false)
                $approval = ApprovedApplicantApplication::create([
                    'benefits' => $benefitsValue,
                    'has_accepted_scholarship' => false, // stays false until user explicitly accepts
                    'scholarship_id' => $scholarship->scholarship_id,
                    'application_id' => $application->application_id,
                    'user_id' => $targetUser->user_id,
                ]);

                // 4) Archive the applicant
                $applicant->is_archive = true;
                $applicant->save();

                // 5) Update user role to STUDENT (support enum cast or string)
                $targetUser->role = is_object($targetUser->role)
                    ? UserRole::STUDENT
                    : UserRole::STUDENT->value;
                $targetUser->save();

                // 6) Create Student record
                $student = Student::create([
                    'user_id' => $targetUser->user_id,
                    'applicant_id' => $applicant->applicant_id,
                    'approved_application_id' => $approval->approved_application_id,
                ]);

                // 7) Append FINAL_APPROVAL status trail
                ApplicantApplicationStatus::create([
                    'user_id' => $targetUser->user_id,
                    'status_name' => ApplicationStatus::FINAL_APPROVAL->value,
                    'date' => now(),
                    'comment' => $data['comment'] ?? 'Final approval granted',
                ]);

                // Optional eager-load
                $approval->load(['scholarship', 'application', 'user', 'student']);

                return response()->json([
                    'message' => 'Applicant approved and promoted to student successfully',
                    'approved_application' => $approval,
                    'student' => $student,
                    'user' => [
                        'user_id' => $targetUser->user_id,
                        'role' => is_object($targetUser->role) ? $targetUser->role->value : (string) $targetUser->role,
                    ],
                ], 201);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to finalize approval',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
