<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Semester;
use App\Models\Student;
use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    /**
     * Student-only: List semesters with transcript URLs.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleValue = is_object($user->role) ? $user->role->value : $user->role;
        if ($roleValue !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can view semesters'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $semesters = Semester::where('user_id', $user->user_id)
            ->orderBy('semester_number')
            ->orderBy('starting_date')
            ->get()
            ->map(function (Semester $semester) {
                $semester->setAttribute(
                    'transcript_url',
                    $semester->transcript_path ? Storage::disk('s3')->url($semester->transcript_path) : null
                );
                return $semester;
            });

        return response()->json([
            'semesters' => $semesters,
        ]);
    }

    /**
     * Student-only: Create a semester record, upload transcript, and append status trail.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleValue = is_object($user->role) ? $user->role->value : $user->role;
        if ($roleValue !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can add semesters'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $validated = $request->validate([
            'credit_hours' => ['required', 'numeric', 'min:0'],
            'total_subjects' => ['required', 'integer', 'min:0'],
            'starting_date' => ['required', 'date'],
            'ending_date' => ['required', 'date', 'after_or_equal:starting_date'],
            'status' => ['required', Rule::in(['completed', 'active'])],
            'semester_number' => ['required', 'integer', 'min:1'],
            'cgpa' => ['required_if:status,completed', 'numeric', 'min:0', 'lte:cgpa_out_of'],
            'cgpa_out_of' => ['required_if:status,completed', 'numeric', 'gt:0'],
            'transcript' => ['required_if:status,completed', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:10240'],
        ]);

        $transcriptPath = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('transcript')) {
                $file = $request->file('transcript');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $transcriptPath = $file->storeAs(
                    "students/{$student->student_id}/semesters",
                    $filename,
                    's3'
                );
            }

            $semester = Semester::create([
                'credit_hours' => $validated['credit_hours'],
                'total_subjects' => $validated['total_subjects'],
                'status' => $validated['status'],
                'cgpa' => $validated['status'] === 'completed' ? $validated['cgpa'] : null,
                'cgpa_out_of' => $validated['status'] === 'completed' ? $validated['cgpa_out_of'] : null,
                'semester_number' => $validated['semester_number'],
                'starting_date' => $validated['starting_date'],
                'ending_date' => $validated['ending_date'],
                'transcript_path' => $transcriptPath,
                'user_id' => $user->user_id,
            ]);

            $this->syncSemesterStatuses(
                $user->user_id,
                (int) $validated['semester_number'],
                $validated['status'],
                $validated['starting_date'],
                $validated['status'] === 'completed' ? $validated['ending_date'] : null
            );

            DB::commit();

            $semester->setAttribute('transcript_url', $transcriptPath ? Storage::disk('s3')->url($transcriptPath) : null);

            return response()->json([
                'message' => 'Semester added successfully',
                'semester' => $semester,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($transcriptPath) {
                Storage::disk('s3')->delete($transcriptPath);
            }

            return response()->json([
                'message' => 'Failed to add semester',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Student-only: Update a semester record and keep status trail in sync.
     */
    public function update(Request $request, Semester $semester)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleValue = is_object($user->role) ? $user->role->value : $user->role;
        if ($roleValue !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can update semesters'], 403);
        }

        if ($semester->user_id !== $user->user_id) {
            return response()->json(['message' => 'You can only update your own semesters'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $payload = [
            'credit_hours' => $request->input('credit_hours', $semester->credit_hours),
            'total_subjects' => $request->input('total_subjects', $semester->total_subjects),
            'starting_date' => $request->input('starting_date', optional($semester->starting_date)->toDateString()),
            'ending_date' => $request->input('ending_date', optional($semester->ending_date)->toDateString()),
            'status' => $request->input('status', $semester->status),
            'semester_number' => $request->input('semester_number', $semester->semester_number),
            'cgpa' => $request->has('cgpa') ? $request->input('cgpa') : $semester->cgpa,
            'cgpa_out_of' => $request->has('cgpa_out_of') ? $request->input('cgpa_out_of') : $semester->cgpa_out_of,
        ];

        $finalStatus = $payload['status'];

        $validator = Validator::make($payload, [
            'credit_hours' => ['required', 'numeric', 'min:0'],
            'total_subjects' => ['required', 'integer', 'min:0'],
            'starting_date' => ['required', 'date'],
            'ending_date' => ['required', 'date', 'after_or_equal:starting_date'],
            'status' => ['required', Rule::in(['completed', 'active'])],
            'semester_number' => ['required', 'integer', 'min:1'],
            'cgpa' => [Rule::requiredIf($finalStatus === 'completed'), 'nullable', 'numeric', 'min:0', 'lte:cgpa_out_of'],
            'cgpa_out_of' => [Rule::requiredIf($finalStatus === 'completed'), 'nullable', 'numeric', 'gt:0'],
        ]);

        $validated = $validator->validate();

        if (
            $finalStatus === 'completed'
            && !$request->hasFile('transcript')
            && empty($semester->transcript_path)
        ) {
            return response()->json([
                'message' => 'Transcript is required when completing a semester',
                'errors' => ['transcript' => ['Transcript file is required for completed semesters.']],
            ], 422);
        }

        $oldTranscriptPath = $semester->transcript_path;
        $newTranscriptPath = null;
        $deleteOldAfterCommit = false;
        $originalSemesterNumber = $semester->semester_number;

        try {
            DB::beginTransaction();

            if ($request->hasFile('transcript')) {
                $file = $request->file('transcript');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $newTranscriptPath = $file->storeAs(
                    "students/{$student->student_id}/semesters",
                    $filename,
                    's3'
                );
            }

            $transcriptPathToSave = $finalStatus === 'completed'
                ? ($newTranscriptPath ?? $oldTranscriptPath)
                : null;

            if ($finalStatus !== 'completed' && $oldTranscriptPath) {
                $deleteOldAfterCommit = true;
            } elseif ($newTranscriptPath && $oldTranscriptPath && $oldTranscriptPath !== $newTranscriptPath) {
                $deleteOldAfterCommit = true;
            }

            $semester->update([
                'credit_hours' => $validated['credit_hours'],
                'total_subjects' => $validated['total_subjects'],
                'status' => $finalStatus,
                'cgpa' => $finalStatus === 'completed' ? $validated['cgpa'] : null,
                'cgpa_out_of' => $finalStatus === 'completed' ? $validated['cgpa_out_of'] : null,
                'semester_number' => $validated['semester_number'],
                'starting_date' => $validated['starting_date'],
                'ending_date' => $validated['ending_date'],
                'transcript_path' => $transcriptPathToSave,
            ]);

            $this->syncSemesterStatuses(
                $user->user_id,
                (int) $validated['semester_number'],
                $finalStatus,
                $validated['starting_date'],
                $finalStatus === 'completed' ? $validated['ending_date'] : null,
                (int) $originalSemesterNumber
            );

            DB::commit();

            if ($deleteOldAfterCommit && $oldTranscriptPath && $oldTranscriptPath !== $newTranscriptPath) {
                Storage::disk('s3')->delete($oldTranscriptPath);
            }

            $semester->refresh();
            $semester->setAttribute('transcript_url', $semester->transcript_path ? Storage::disk('s3')->url($semester->transcript_path) : null);

            return response()->json([
                'message' => 'Semester updated successfully',
                'semester' => $semester,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($newTranscriptPath) {
                Storage::disk('s3')->delete($newTranscriptPath);
            }

            return response()->json([
                'message' => 'Failed to update semester',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Student-only: Delete a semester record, its transcript, and related statuses.
     */
    public function destroy(Request $request, Semester $semester)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $roleValue = is_object($user->role) ? $user->role->value : $user->role;
        if ($roleValue !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can delete semesters'], 403);
        }

        if ($semester->user_id !== $user->user_id) {
            return response()->json(['message' => 'You can only delete your own semesters'], 403);
        }

        $transcriptPath = $semester->transcript_path;
        $semesterNumber = (int) $semester->semester_number;
        $statusNameStarted = ApplicationStatus::STARTED_SEMESTER->value;
        $statusNameCompleted = ApplicationStatus::COMPLETED_SEMESTER->value;
        $startComment = "Started the {$semesterNumber} semester";
        $completedComment = "Finished the {$semesterNumber} semester";

        try {
            DB::beginTransaction();

            UserStatus::where('user_id', $user->user_id)
                ->whereIn('status_name', [$statusNameStarted, $statusNameCompleted])
                ->whereIn('comment', [$startComment, $completedComment])
                ->delete();

            $semester->delete();

            DB::commit();

            if ($transcriptPath) {
                Storage::disk('s3')->delete($transcriptPath);
            }

            return response()->json([
                'message' => 'Semester deleted successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete semester',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ensure semester-related user statuses reflect the latest data.
     */
    private function syncSemesterStatuses(
        int $userId,
        int $semesterNumber,
        string $status,
        string $startingDate,
        ?string $endingDate,
        ?int $originalSemesterNumber = null
    ): void {
        $status = strtolower($status);
        $startedStatusName = ApplicationStatus::STARTED_SEMESTER->value;
        $completedStatusName = ApplicationStatus::COMPLETED_SEMESTER->value;

        $originalSemesterNumber = $originalSemesterNumber ?? $semesterNumber;

        $startCommentNew = "Started the {$semesterNumber} semester";
        $startCommentOld = "Started the {$originalSemesterNumber} semester";
        $startComments = array_values(array_unique([$startCommentNew, $startCommentOld]));

        $startedStatus = UserStatus::where('user_id', $userId)
            ->where('status_name', $startedStatusName)
            ->whereIn('comment', $startComments)
            ->orderByDesc('date')
            ->first();

        $startDateTime = Carbon::parse($startingDate)->startOfDay();

        if ($startedStatus) {
            $startedStatus->update([
                'status_name' => $startedStatusName,
                'date' => $startDateTime,
                'comment' => $startCommentNew,
            ]);
        } else {
            UserStatus::create([
                'user_id' => $userId,
                'status_name' => $startedStatusName,
                'date' => $startDateTime,
                'comment' => $startCommentNew,
            ]);
        }

        $completedCommentNew = "Finished the {$semesterNumber} semester";
        $completedCommentOld = "Finished the {$originalSemesterNumber} semester";
        $completedComments = array_values(array_unique([$completedCommentNew, $completedCommentOld]));

        $completedStatus = UserStatus::where('user_id', $userId)
            ->where('status_name', $completedStatusName)
            ->whereIn('comment', $completedComments)
            ->orderByDesc('date')
            ->first();

        if ($status === 'completed') {
            if (!$endingDate) {
                throw new \InvalidArgumentException('Ending date is required for completed semesters.');
            }

            $endDateTime = Carbon::parse($endingDate)->startOfDay();

            if ($completedStatus) {
                $completedStatus->update([
                    'status_name' => $completedStatusName,
                    'date' => $endDateTime,
                    'comment' => $completedCommentNew,
                ]);
            } else {
                UserStatus::create([
                    'user_id' => $userId,
                    'status_name' => $completedStatusName,
                    'date' => $endDateTime,
                    'comment' => $completedCommentNew,
                ]);
            }
        } elseif ($completedStatus) {
            $completedStatus->delete();
        }
    }
}
