<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request as StudentRequest;
use App\Models\RequestStatusTrail;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    /**
     * Student: Submit a new request.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role?->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can submit requests'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $data = $request->validate([
            'request_type' => ['required', 'string', 'max:150'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'body' => ['required', 'string'],
            'document_path' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            $requestModel = StudentRequest::create([
                'student_id' => $student->student_id,
                'request_type' => $data['request_type'],
                'amount' => $data['amount'] ?? null,
                'body' => $data['body'],
                'current_status' => RequestStatus::SUBMITTED->value,
                'document_path' => $data['document_path'] ?? null,
            ]);

            RequestStatusTrail::create([
                'request_id' => $requestModel->request_id,
                'status' => RequestStatus::SUBMITTED->value,
                'comment' => null,
                'date' => now(),
                'document_path' => null,
            ]);

            DB::commit();

            $requestModel->load('statusTrails');

            return response()->json([
                'message' => 'Request submitted successfully',
                'request' => $requestModel,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to submit request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Student: List own requests with latest status trail entries.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role?->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can view their requests'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $requests = StudentRequest::with('statusTrails')
            ->where('student_id', $student->student_id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['requests' => $requests]);
    }

    /**
     * Student: View a specific request and its status history.
     */
    public function show(Request $request, int $requestId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role?->value !== UserRole::STUDENT->value) {
            return response()->json(['message' => 'Only students can view request details'], 403);
        }

        $student = Student::where('user_id', $user->user_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $studentRequest = StudentRequest::with('statusTrails')
            ->where('student_id', $student->student_id)
            ->where('request_id', $requestId)
            ->first();

        if (!$studentRequest) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        return response()->json(['request' => $studentRequest]);
    }
}
