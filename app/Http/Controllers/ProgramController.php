<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                return $this->formatProgramResponse($program);
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

            // Ensure boolean values are properly cast
            $data['enable_qr_attendance'] = filter_var($data['enable_qr_attendance'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $data['generate_certificates'] = filter_var($data['generate_certificates'] ?? false, FILTER_VALIDATE_BOOLEAN);

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

        $program = Program::with(['programApplications.student.user'])->find($id);

        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        return response()->json([
            'program' => $this->formatProgramResponse($program)
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

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'discription' => ['nullable', 'string'],
            'date' => ['sometimes', 'date'],
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
                // Delete old image if exists
                if ($program->image_file && Storage::disk('public')->exists($program->image_file)) {
                    Storage::disk('public')->delete($program->image_file);
                }

                $imageFile = $request->file('image_file');
                $imagePath = $imageFile->store('programs/images', 'public');
                $data['image_file'] = $imagePath;
            }

            // Ensure boolean values are properly cast for updates
            if (isset($data['enable_qr_attendance'])) {
                $data['enable_qr_attendance'] = filter_var($data['enable_qr_attendance'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($data['generate_certificates'])) {
                $data['generate_certificates'] = filter_var($data['generate_certificates'], FILTER_VALIDATE_BOOLEAN);
            }

            // Generate QR URL if QR attendance is being enabled
            if (isset($data['enable_qr_attendance']) && $data['enable_qr_attendance'] && !$program->qr_url) {
                $data['qr_url'] = $this->generateQRUrl();
            }

            $program->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Program updated successfully',
                'program' => $this->formatProgramResponse($program->load('programApplications'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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
        return url("/api/v1/programs/qr/{$token}");
    }

    /**
     * Public: QR Code scanning endpoint (no authentication required)
     */
    public function qrScan(Request $request, $token)
    {
        // Find program by QR token
        $program = Program::where('qr_url', 'like', "%{$token}")->first();

        if (!$program) {
            return response()->json(['message' => 'Invalid QR code'], 404);
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
                'date' => $program->date,
                'location' => $program->location,
                'qr_token' => $token
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
