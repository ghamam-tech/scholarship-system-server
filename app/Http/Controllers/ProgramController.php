<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    /**
     * Admin: Get all programs
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view programs'], 403);
        }

        $programs = Program::withCount('programApplications')->get();

        return response()->json([
            'programs' => $programs->map(function ($program) {
                return [
                    'program_id' => $program->program_id,
                    'title' => $program->title,
                    'date' => $program->date,
                    'category' => $program->category,
                    'country' => $program->country,
                    'status' => $program->program_status,
                    'invitations_count' => $program->program_applications_count,
                    'location' => $program->location,
                ];
            })
        ]);
    }

    /**
     * Admin: Create a new program
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can create programs'], 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'discription' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
            'program_coordinatior_name' => ['nullable', 'string', 'max:255'],
            'program_coordinatior_phone' => ['nullable', 'string', 'max:20'],
            'program_coordinatior_email' => ['nullable', 'email', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'enable_qr_attendance' => ['sometimes', 'boolean'],
            'generate_certificates' => ['sometimes', 'boolean'],
        ]);

        try {
            DB::beginTransaction();

            // Handle image file upload
            if ($request->hasFile('image_file')) {
                $imageFile = $request->file('image_file');
                $imagePath = $imageFile->store('programs/images', 'public');
                $data['image_file'] = $imagePath;
            }

            // Handle boolean values for JSON
            $data['enable_qr_attendance'] = (bool) ($data['enable_qr_attendance'] ?? false);
            $data['generate_certificates'] = (bool) ($data['generate_certificates'] ?? false);

            // Generate QR URL if QR attendance is enabled
            if ($data['enable_qr_attendance']) {
                $data['qr_url'] = $this->generateQRUrl();
            }

            // Set default program status
            $data['program_status'] = 'active';

            $program = Program::create($data);

            DB::commit();

            return response()->json([
                'message' => 'Program created successfully',
                'program' => $this->formatProgramResponse($program->load('programApplications'))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get specific program by ID
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view program details'], 403);
        }

        $program = Program::with(['programApplications'])->find($id);

        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        return response()->json([
            'program' => [
                'program_id' => $program->program_id,
                'discription' => $program->discription,
                'title' => $program->title,
                'date' => $program->date,
                'category' => $program->category,
                'country' => $program->country,
                'status' => $program->program_status,
                'location' => $program->location,
                'invitations_count' => $program->program_applications_count,
                'enable_qr_attendance' => $program->enable_qr_attendance,
                'generate_certificates' => $program->generate_certificates,
                'program_coordinatior_name' => $program->program_coordinatior_name,
                'program_coordinatior_phone' => $program->program_coordinatior_phone,
                'program_coordinatior_email' => $program->program_coordinatior_email,
                'start_date' => $program->start_date,
                'end_date' => $program->end_date,
                'image_file' => $program->image_file,
                'image_url' => $program->image_file ? asset('storage/' . $program->image_file) : null,
                'qr_url' => $program->qr_url,
                'applications' => $program->programApplications->map(function ($application) {
                    return [
                        'application_id' => $application->application_program_id,
                        'student_id' => $application->student_id,
                        'status' => $application->application_status
                    ];
                })
            ]
        ]);
    }

    /**
     * Admin: Update program
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can update programs'], 403);
        }

        $program = Program::find($id);

        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        // Simplified validation for JSON requests only
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'discription' => ['sometimes', 'string'],
            'date' => ['sometimes', 'date'],
            'location' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:255'],
            'program_coordinatior_name' => ['sometimes', 'string', 'max:255'],
            'program_coordinatior_phone' => ['sometimes', 'string', 'max:20'],
            'program_coordinatior_email' => ['sometimes', 'email', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'program_status' => ['sometimes', 'in:active,inactive,completed,cancelled'],
            'enable_qr_attendance' => ['sometimes', 'boolean'],
            'generate_certificates' => ['sometimes', 'boolean'],

        ]);

        try {
            DB::beginTransaction();

            // Debug logging
            \Log::info('Update Request Data:', $data);
            \Log::info('Request All Data:', $request->all());

            // Handle boolean values for JSON
            if (isset($data['enable_qr_attendance'])) {
                $data['enable_qr_attendance'] = (bool) $data['enable_qr_attendance'];
            }

            if (isset($data['generate_certificates'])) {
                $data['generate_certificates'] = (bool) $data['generate_certificates'];
            }

            // Handle program_status updates
            if (isset($data['program_status'])) {
                $validStatuses = ['active', 'inactive', 'completed', 'cancelled'];
                if (!in_array($data['program_status'], $validStatuses)) {
                    unset($data['program_status']);
                }
            }

            // Generate QR URL if QR attendance is being enabled
            if (isset($data['enable_qr_attendance']) && $data['enable_qr_attendance'] && !$program->qr_url) {
                $data['qr_url'] = $this->generateQRUrl();
            }

            // Debug: Log what data we're about to update
            \Log::info('Data being sent to update:', $data);

            // Update the program
            $program->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Program updated successfully',
                'program' => $this->formatProgramResponse($program->fresh()->load('programApplications'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Failed to update program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Delete program
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can delete programs'], 403);
        }

        $program = Program::find($id);

        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        try {
            // Delete associated image file if exists
            if ($program->image_file && Storage::disk('public')->exists($program->image_file)) {
                Storage::disk('public')->delete($program->image_file);
            }

            $program->delete();

            return response()->json([
                'message' => 'Program deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete program',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Change program status
     */
    public function changeStatus(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can change program status'], 403);
        }

        $program = Program::find($id);

        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        $data = $request->validate([
            'program_status' => ['required', 'in:active,inactive,completed,cancelled']
        ]);

        try {
            $program->update(['program_status' => $data['program_status']]);

            return response()->json([
                'message' => 'Program status updated successfully',
                'program' => $this->formatProgramResponse($program)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update program status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get program statistics
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view program statistics'], 403);
        }

        $statistics = [
            'total_programs' => Program::count(),
            'active_programs' => Program::where('program_status', 'active')->count(),
            'completed_programs' => Program::where('program_status', 'completed')->count(),
            'cancelled_programs' => Program::where('program_status', 'cancelled')->count(),
            'total_applications' => DB::table('program_applications')->count(),
            'pending_applications' => DB::table('program_applications')->where('application_status', 'pending')->count(),
            'approved_applications' => DB::table('program_applications')->where('application_status', 'approved')->count(),
        ];

        return response()->json([
            'statistics' => $statistics
        ]);
    }

    /**
     * Generate a unique QR URL for the program
     */
    private function generateQRUrl()
    {
        $token = Str::random(32);
        return $token;
    }

    /**
     * Public: QR Code scanning endpoint (no authentication required)
     * Returns program information for QR scan
     * Only works when program status is "active"
     */
    public function qrScan(Request $request, $token)
    {
        // Find program by QR token
        $program = Program::where('qr_url', $token)->first();

        if (!$program) {
            return response()->json(['message' => 'Invalid QR code'], 404);
        }

        // Check if program is active
        if ($program->program_status !== 'active') {
            return response()->json([
                'message' => 'QR code scanning is not available',
                'reason' => 'Program is not active',
                'program_status' => $program->program_status,
                'available_when' => 'Program status is "active"'
            ], 403);
        }

        // Check if QR attendance is enabled
        if (!$program->enable_qr_attendance) {
            return response()->json(['message' => 'QR attendance is not enabled for this program'], 400);
        }

        // Return program info for QR scan
        return response()->json([
            'message' => 'QR code scanned successfully',
            'program' => [
                'program_id' => $program->program_id,
                'title' => $program->title,
                'description' => $program->discription,
                'date' => $program->date,
                'location' => $program->location,
                'country' => $program->country,
                'category' => $program->category,
                'program_status' => $program->program_status,
                'qr_token' => $token,
                'enable_qr_attendance' => $program->enable_qr_attendance,
                'generate_certificates' => $program->generate_certificates,
                'coordinator_name' => $program->program_coordinatior_name,
                'coordinator_phone' => $program->program_coordinatior_phone,
                'coordinator_email' => $program->program_coordinatior_email,
                'image_file' => $program->image_file,
                'image_url' => $program->image_file ? asset('storage/' . $program->image_file) : null,
            ]
        ]);
    }

    

    /**
     * Format program response to ensure proper boolean values
     */
    private function formatProgramResponse($program)
    {
        $programArray = $program->toArray();

        // Ensure boolean fields are properly formatted
        $programArray['enable_qr_attendance'] = (bool) $program->enable_qr_attendance;
        $programArray['generate_certificates'] = (bool) $program->generate_certificates;

        // Add image URL if image exists
        if ($program->image_file) {
            $programArray['image_url'] = $program->image_url;
        }

        return $programArray;
    }
}
