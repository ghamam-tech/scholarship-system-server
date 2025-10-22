<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Admin: Create a new student with email and password only
     */
    public function createStudent(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can create students'], 403);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        try {
            DB::beginTransaction();

            // Create user with student role
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::STUDENT,
                'timezone' => 'UTC', // Default timezone
            ]);

            // Create student record linked to the user
            $student = Student::create([
                'user_id' => $user->user_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Student created successfully',
                'student' => [
                    'student_id' => $student->student_id,
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'role' => $user->role->value,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get all students
     */
    public function getAllStudents(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view all students'], 403);
        }

        $students = Student::with('user')->get();

        return response()->json([
            'students' => $students
        ]);
    }

    /**
     * Admin: Get specific student by ID
     */
    public function getStudentById(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view student details'], 403);
        }

        $student = Student::with('user')->find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'student' => $student
        ]);
    }
}
