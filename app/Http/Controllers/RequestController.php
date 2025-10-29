<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request as StudentRequest;
use App\Models\RequestStatusTrail;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'document' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        $documentPath = null;

        try {
            DB::beginTransaction();

            $requestModel = StudentRequest::create([
                'student_id' => $student->student_id,
                'request_type' => $data['request_type'],
                'amount' => $data['amount'] ?? null,
                'body' => $data['body'],
                'current_status' => RequestStatus::SUBMITTED->value,
            ]);

            $documentPath = $this->storeRequestDocument($request, $requestModel);

            if ($documentPath) {
                $requestModel->update([
                    'document_path' => $documentPath,
                ]);
            }

            RequestStatusTrail::create([
                'request_id' => $requestModel->request_id,
                'status' => RequestStatus::SUBMITTED->value,
                'comment' => null,
                'date' => now(),
                'document_path' => $documentPath,
            ]);

            DB::commit();

            $requestModel->load('statusTrails');

            return response()->json([
                'message' => 'Request submitted successfully',
                'request' => $requestModel,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($documentPath) {
                Storage::disk('s3')->delete($documentPath);
            }

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

    /**
     * Admin: List all student requests.
     */
    public function adminIndex(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role?->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can view all requests'], 403);
        }

        $requests = StudentRequest::with([
                'student.user',
                'student.applicant',
                'statusTrails',
            ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['requests' => $requests]);
    }

    /**
     * Admin: View a specific request with full history.
     */
    public function adminShow(Request $request, int $requestId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role?->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can view request details'], 403);
        }

        $studentRequest = StudentRequest::with([
                'student.user',
                'student.applicant',
                'statusTrails',
            ])
            ->find($requestId);

        if (!$studentRequest) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        return response()->json(['request' => $studentRequest]);
    }

    /**
     * Admin: Change the status of a request, recording the history entry.
     */
    public function updateStatus(Request $request, int $requestId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->role?->value !== UserRole::ADMIN->value) {
            return response()->json(['message' => 'Only admins can update request status'], 403);
        }

        $studentRequest = StudentRequest::find($requestId);

        if (!$studentRequest) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        $statusValues = array_map(static fn (RequestStatus $status) => $status->value, RequestStatus::cases());

        $data = $request->validate([
            'status' => ['required', Rule::in($statusValues)],
            'comment' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'date' => ['nullable', 'date'],
        ]);

        $documentPath = null;

        try {
            DB::beginTransaction();

            $statusEnum = RequestStatus::from($data['status']);
            $historyDate = isset($data['date']) ? Carbon::parse($data['date']) : now();

            $updatePayload = [
                'current_status' => $statusEnum->value,
            ];

            $documentPath = $this->storeRequestDocument($request, $studentRequest);

            if ($documentPath) {
                $updatePayload['document_path'] = $documentPath;
            }

            $studentRequest->update($updatePayload);

            RequestStatusTrail::create([
                'request_id' => $studentRequest->request_id,
                'status' => $statusEnum->value,
                'comment' => $data['comment'] ?? null,
                'date' => $historyDate,
                'document_path' => $documentPath,
            ]);

            DB::commit();

            $studentRequest->load(['statusTrails', 'student.user', 'student.applicant']);

            return response()->json([
                'message' => 'Request status updated successfully',
                'request' => $studentRequest,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($documentPath) {
                Storage::disk('s3')->delete($documentPath);
            }

            return response()->json([
                'message' => 'Failed to update request status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function storeRequestDocument(Request $request, StudentRequest $studentRequest, string $field = 'document'): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        if (!$file instanceof UploadedFile) {
            return null;
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $sanitizedBase = Str::slug($originalName) ?: 'document';
        $filename = now()->format('YmdHis') . '_' . uniqid() . '_' . $sanitizedBase;

        if ($extension) {
            $filename .= '.' . $extension;
        }

        return $file->storeAs(
            "students/{$studentRequest->student_id}/requests/{$studentRequest->request_id}",
            $filename,
            's3'
        );
    }
}
