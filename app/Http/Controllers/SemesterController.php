<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SemesterController extends Controller
{
    /**
     * Get student semesters
     */
    public function getStudentSemesters(Request $request, $studentId)
    {
        $student = Student::with(['semesters' => function ($query) {
            $query->orderBy('semester_no', 'asc');
        }])->findOrFail($studentId);

        return response()->json([
            'student_id' => $student->student_id,
            'student_name' => $student->ar_name ?? $student->en_name,
            'semesters' => $student->semesters,
            'total_semesters' => $student->semesters->count(),
            'active_semester' => $student->activeSemester,
            'completed_semesters' => $student->semesters->where('status', 'completed')->count()
        ]);
    }

    /**
     * Create new semester for student
     */
    public function createSemester(Request $request, $studentId)
    {
        $data = $request->validate([
            'semester_no' => ['required', 'integer', 'min:1'],
            'courses' => ['required', 'integer', 'min:0'],
            'credits' => ['required', 'integer', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'status' => ['nullable', 'string', 'in:active,completed,failed,withdrawn'],
            'transcript' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000']
        ]);

        $student = Student::findOrFail($studentId);

        // Check if semester number already exists for this student
        if ($student->semesters()->where('semester_no', $data['semester_no'])->exists()) {
            throw ValidationException::withMessages([
                'semester_no' => 'Semester number already exists for this student'
            ]);
        }

        try {
            DB::transaction(function () use ($student, $data, $request) {
                $semester = Semester::create([
                    'student_id' => $student->student_id,
                    'semester_no' => $data['semester_no'],
                    'courses' => $data['courses'],
                    'credits' => $data['credits'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'cgpa' => $data['cgpa'] ?? null,
                    'status' => $data['status'] ?? 'active',
                    'notes' => $data['notes'] ?? null
                ]);

                // Handle transcript upload
                if ($request->hasFile('transcript')) {
                    $file = $request->file('transcript');
                    $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $path = $file->storeAs(
                        "students/{$student->student_id}/semesters/{$semester->semester_id}/transcripts",
                        $filename,
                        's3'
                    );

                    $semester->update([
                        'transcript' => config('filesystems.disks.s3.url') . '/' . $path
                    ]);
                }
            });

            return response()->json([
                'message' => 'Semester created successfully',
                'semester' => $student->fresh()->semesters()->latest()->first()
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create semester',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update semester
     */
    public function updateSemester(Request $request, $semesterId)
    {
        $data = $request->validate([
            'courses' => ['sometimes', 'integer', 'min:0'],
            'credits' => ['sometimes', 'integer', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'status' => ['sometimes', 'string', 'in:active,completed,failed,withdrawn'],
            'transcript' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000']
        ]);

        $semester = Semester::findOrFail($semesterId);

        try {
            DB::transaction(function () use ($semester, $data, $request) {
                // Handle transcript upload
                if ($request->hasFile('transcript')) {
                    // Delete old transcript if exists
                    if ($semester->transcript) {
                        Storage::disk('s3')->delete($semester->transcript);
                    }

                    $file = $request->file('transcript');
                    $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $path = $file->storeAs(
                        "students/{$semester->student_id}/semesters/{$semester->semester_id}/transcripts",
                        $filename,
                        's3'
                    );

                    $data['transcript'] = config('filesystems.disks.s3.url') . '/' . $path;
                }

                $semester->update($data);
            });

            return response()->json([
                'message' => 'Semester updated successfully',
                'semester' => $semester->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update semester',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get semester statistics
     */
    public function getSemesterStatistics(Request $request, $studentId)
    {
        $student = Student::with('semesters')->findOrFail($studentId);

        $semesters = $student->semesters;

        $statistics = [
            'total_semesters' => $semesters->count(),
            'active_semesters' => $semesters->where('status', 'active')->count(),
            'completed_semesters' => $semesters->where('status', 'completed')->count(),
            'failed_semesters' => $semesters->where('status', 'failed')->count(),
            'withdrawn_semesters' => $semesters->where('status', 'withdrawn')->count(),
            'total_courses' => $semesters->sum('courses'),
            'total_credits' => $semesters->sum('credits'),
            'average_cgpa' => $semesters->whereNotNull('cgpa')->avg('cgpa'),
            'highest_cgpa' => $semesters->whereNotNull('cgpa')->max('cgpa'),
            'lowest_cgpa' => $semesters->whereNotNull('cgpa')->min('cgpa'),
            'current_semester' => $semesters->where('status', 'active')->first(),
            'latest_semester' => $semesters->sortByDesc('semester_no')->first()
        ];

        return response()->json([
            'student_id' => $student->student_id,
            'student_name' => $student->ar_name ?? $student->en_name,
            'statistics' => $statistics
        ]);
    }

    /**
     * Get all active semesters across all students
     */
    public function getAllActiveSemesters(Request $request)
    {
        $activeSemesters = Semester::with(['student.user'])
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'active_semesters' => $activeSemesters,
            'count' => $activeSemesters->count()
        ]);
    }

    // ========== STUDENT ROUTES ==========

    /**
     * Get student's own semesters
     */
    public function getMySemesters(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $semesters = $student->semesters()->orderBy('semester_no', 'asc')->get();

        return response()->json([
            'student_id' => $student->student_id,
            'student_name' => $student->ar_name ?? $student->en_name,
            'semesters' => $semesters,
            'total_semesters' => $semesters->count(),
            'active_semester' => $semesters->where('status', 'active')->first(),
            'completed_semesters' => $semesters->where('status', 'completed')->count()
        ]);
    }

    /**
     * Create new semester for student (student creates their own)
     */
    public function createMySemester(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $data = $request->validate([
            'semester_no' => ['required', 'integer', 'min:1'],
            'courses' => ['required', 'integer', 'min:0'],
            'credits' => ['required', 'integer', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'status' => ['nullable', 'string', 'in:active,completed,failed,withdrawn'],
            'transcript' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000']
        ]);

        // Check if semester number already exists for this student
        if ($student->semesters()->where('semester_no', $data['semester_no'])->exists()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'semester_no' => ['Semester number already exists for this student']
                ]
            ], 422);
        }

        try {
            DB::transaction(function () use ($student, $data, $request) {
                $semester = Semester::create([
                    'student_id' => $student->student_id,
                    'semester_no' => $data['semester_no'],
                    'courses' => $data['courses'],
                    'credits' => $data['credits'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'cgpa' => $data['cgpa'] ?? null,
                    'status' => $data['status'] ?? 'active',
                    'notes' => $data['notes'] ?? null
                ]);

                // Handle transcript upload
                if ($request->hasFile('transcript')) {
                    $file = $request->file('transcript');
                    $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $path = $file->storeAs(
                        "students/{$student->student_id}/semesters/{$semester->semester_id}/transcripts",
                        $filename,
                        's3'
                    );

                    $semester->update([
                        'transcript' => config('filesystems.disks.s3.url') . '/' . $path
                    ]);
                }
            });

            return response()->json([
                'message' => 'Semester created successfully',
                'semester' => $student->fresh()->semesters()->latest()->first()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create semester',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update student's own semester
     */
    public function updateMySemester(Request $request, $semesterId)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $semester = $student->semesters()->findOrFail($semesterId);

        $data = $request->validate([
            'courses' => ['sometimes', 'integer', 'min:0'],
            'credits' => ['sometimes', 'integer', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'cgpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'status' => ['sometimes', 'string', 'in:active,completed,failed,withdrawn'],
            'transcript' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000']
        ]);

        try {
            DB::transaction(function () use ($semester, $data, $request) {
                // Handle transcript upload
                if ($request->hasFile('transcript')) {
                    // Delete old transcript if exists
                    if ($semester->transcript) {
                        Storage::disk('s3')->delete($semester->transcript);
                    }

                    $file = $request->file('transcript');
                    $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $path = $file->storeAs(
                        "students/{$semester->student_id}/semesters/{$semester->semester_id}/transcripts",
                        $filename,
                        's3'
                    );

                    $data['transcript'] = config('filesystems.disks.s3.url') . '/' . $path;
                }

                $semester->update($data);
            });

            return response()->json([
                'message' => 'Semester updated successfully',
                'semester' => $semester->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update semester',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's own semester statistics
     */
    public function getMySemesterStatistics(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $semesters = $student->semesters;

        $statistics = [
            'total_semesters' => $semesters->count(),
            'active_semesters' => $semesters->where('status', 'active')->count(),
            'completed_semesters' => $semesters->where('status', 'completed')->count(),
            'failed_semesters' => $semesters->where('status', 'failed')->count(),
            'withdrawn_semesters' => $semesters->where('status', 'withdrawn')->count(),
            'total_courses' => $semesters->sum('courses'),
            'total_credits' => $semesters->sum('credits'),
            'average_cgpa' => $semesters->whereNotNull('cgpa')->avg('cgpa'),
            'highest_cgpa' => $semesters->whereNotNull('cgpa')->max('cgpa'),
            'lowest_cgpa' => $semesters->whereNotNull('cgpa')->min('cgpa'),
            'current_semester' => $semesters->where('status', 'active')->first(),
            'latest_semester' => $semesters->sortByDesc('semester_no')->first()
        ];

        return response()->json([
            'student_id' => $student->student_id,
            'student_name' => $student->ar_name ?? $student->en_name,
            'statistics' => $statistics
        ]);
    }
}
