<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Student;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;

class TicketController extends Controller
{
    private const STATUSES = ['open', 'processing', 'resolved', 'closed'];
    private const PRIORITIES = ['low', 'medium', 'high'];

    /**
     * Student: List own tickets.
     */
    public function studentIndex(Request $request)
    {
        $student = $this->getAuthenticatedStudent($request);

        $tickets = Ticket::withCount('messages')
            ->where('student_id', $student->student_id)
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['tickets' => $tickets]);
    }

    /**
     * Student: Create a new ticket (optional first message).
     */
    public function studentStore(Request $request)
    {
        $student = $this->getAuthenticatedStudent($request);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', 'in:' . implode(',', self::PRIORITIES)],
            'message' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $ticket = Ticket::create([
                'student_id' => $student->student_id,
                'subject' => $data['subject'],
                'priority' => $data['priority'],
                'status' => 'open',
            ]);

            if (!empty($data['message'])) {
                TicketMessage::create([
                    'ticket_id' => $ticket->ticket_id,
                    'user_id' => $request->user()->user_id,
                    'content' => $data['message'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Ticket created successfully',
                'ticket' => $ticket->load('messages'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create ticket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Student: View a specific ticket and its messages.
     */
    public function studentShow(Request $request, int $ticketId)
    {
        $student = $this->getAuthenticatedStudent($request);

        $ticket = Ticket::with(['messages.user'])
            ->where('student_id', $student->student_id)
            ->where('ticket_id', $ticketId)
            ->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        return response()->json(['ticket' => $ticket]);
    }

    /**
     * Student: Reply on an open ticket.
     */
    public function studentReply(Request $request, int $ticketId)
    {
        $student = $this->getAuthenticatedStudent($request);

        $ticket = Ticket::where('student_id', $student->student_id)
            ->where('ticket_id', $ticketId)
            ->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        if ($ticket->status !== 'open') {
            return response()->json(['message' => 'Cannot reply to a closed ticket'], 422);
        }

        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $request->user()->user_id,
            'content' => $data['message'],
        ]);

        $ticket->touch();

        return response()->json([
            'message' => 'Reply added successfully',
            'ticket_message' => $message->load('user'),
        ], 201);
    }

    /**
     * Admin: List all tickets.
     */
    public function adminIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $tickets = Ticket::with(['student.user'])
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['tickets' => $tickets]);
    }

    /**
     * Admin: View a specific ticket with messages.
     */
    public function adminShow(Request $request, int $ticketId)
    {
        $this->ensureAdmin($request);

        $ticket = Ticket::with(['student.user', 'messages.user'])
            ->find($ticketId);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        return response()->json(['ticket' => $ticket]);
    }

    /**
     * Admin: Reply on an open ticket.
     */
    public function adminReply(Request $request, int $ticketId)
    {
        $this->ensureAdmin($request);

        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        if ($ticket->status !== 'open') {
            return response()->json(['message' => 'Cannot reply to a closed ticket'], 422);
        }

        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->ticket_id,
            'user_id' => $request->user()->user_id,
            'content' => $data['message'],
        ]);

        $ticket->touch();

        return response()->json([
            'message' => 'Reply added successfully',
            'ticket_message' => $message->load('user'),
        ], 201);
    }

    /**
     * Admin: Change ticket status.
     */
    public function adminUpdateStatus(Request $request, int $ticketId)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', self::STATUSES)],
        ]);

        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $ticket->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket,
        ]);
    }

    private function getAuthenticatedStudent(Request $request): Student
    {
        $user = $request->user();

        if (!$user) {
            throw new HttpResponseException(response()->json(['message' => 'Unauthenticated'], 401));
        }

        $roleValue = $user->role instanceof UserRole ? $user->role->value : $user->role;

        if ($roleValue !== UserRole::STUDENT->value) {
            throw new HttpResponseException(response()->json(['message' => 'Only students can perform this action'], 403));
        }

        $student = Student::where('user_id', $user->user_id)->first();

        if (!$student) {
            throw new HttpResponseException(response()->json(['message' => 'Student profile not found'], 404));
        }

        return $student;
    }

    private function ensureAdmin(Request $request): void
    {
        $user = $request->user();

        if (!$user) {
            throw new HttpResponseException(response()->json(['message' => 'Unauthenticated'], 401));
        }

        $roleValue = $user->role instanceof UserRole ? $user->role->value : $user->role;

        if ($roleValue !== UserRole::ADMIN->value) {
            throw new HttpResponseException(response()->json(['message' => 'Only admins can perform this action'], 403));
        }
    }
}
