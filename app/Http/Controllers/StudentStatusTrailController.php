<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentStatusTrail;
use App\Enums\StudentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentStatusTrailController extends Controller
{
    /**
     * Get student status trail
     */
    public function getStatusTrail(Request $request, $studentId)
    {
        $student = Student::with(['statusTrails' => function ($query) {
            $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
        }])->findOrFail($studentId);

        return response()->json([
            'student_id' => $student->student_id,
            'student_name' => $student->ar_name ?? $student->en_name,
            'status_trail' => $student->statusTrails,
            'current_status' => $student->currentStatus,
            'total_status_changes' => $student->statusTrails->count()
        ]);
    }

    /**
     * Add new status to student
     */
    public function addStatus(Request $request, $studentId)
    {
        $data = $request->validate([
            'status_name' => ['required', 'string', 'in:active,first_warning,second_warning,request_meeting,graduate_student,suspended,terminated'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'date' => ['nullable', 'date']
        ]);

        $student = Student::findOrFail($studentId);

        try {
            DB::transaction(function () use ($student, $data, $request) {
                StudentStatusTrail::create([
                    'student_id' => $student->student_id,
                    'status_name' => $data['status_name'],
                    'date' => $data['date'] ?? now(),
                    'comment' => $data['comment'],
                    'changed_by' => $request->user()->email ?? 'System'
                ]);
            });

            return response()->json([
                'message' => 'Student status updated successfully',
                'status_trail' => $student->fresh()->statusTrails()->latest('date')->first()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update student status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students by status
     */
    public function getStudentsByStatus(Request $request, $status)
    {
        $students = Student::whereHas('statusTrails', function ($query) use ($status) {
            $query->where('status_name', $status)
                ->whereRaw('date = (SELECT MAX(date) FROM student_status_trails WHERE student_id = students.student_id)');
        })->with(['user', 'currentStatus'])->get();

        return response()->json([
            'status' => $status,
            'students' => $students,
            'count' => $students->count()
        ]);
    }

    /**
     * Get students with warnings
     */
    public function getStudentsWithWarnings(Request $request)
    {
        $students = Student::whereHas('statusTrails', function ($query) {
            $query->whereIn('status_name', ['first_warning', 'second_warning'])
                ->whereRaw('date = (SELECT MAX(date) FROM student_status_trails WHERE student_id = students.student_id)');
        })->with(['user', 'currentStatus'])->get();

        return response()->json([
            'students_with_warnings' => $students,
            'first_warning_count' => $students->where('currentStatus.status_name', 'first_warning')->count(),
            'second_warning_count' => $students->where('currentStatus.status_name', 'second_warning')->count(),
            'total' => $students->count()
        ]);
    }

    /**
     * Get students requesting meetings
     */
    public function getStudentsRequestingMeetings(Request $request)
    {
        $students = Student::whereHas('statusTrails', function ($query) {
            $query->where('status_name', 'request_meeting')
                ->whereRaw('date = (SELECT MAX(date) FROM student_status_trails WHERE student_id = students.student_id)');
        })->with(['user', 'currentStatus'])->get();

        return response()->json([
            'students_requesting_meetings' => $students,
            'count' => $students->count()
        ]);
    }
}
