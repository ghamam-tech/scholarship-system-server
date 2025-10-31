<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OpportunityController extends Controller
{
    /**
     * Admin: Get all opportunities
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view opportunities'], 403);
        }

        $opportunities = Opportunity::withCount('opportunityApplications')->get();

        return response()->json([
            'opportunities' => $opportunities->map(function ($opportunity) {
                return [
                    'opportunity_id' => $opportunity->opportunity_id,
                    'title' => $opportunity->title,
                    'date' => $opportunity->date,
                    'category' => $opportunity->category,
                    'country' => $opportunity->country,
                    'status' => $opportunity->opportunity_status,
                    'invitations_count' => $opportunity->opportunity_applications_count,
                    'location' => $opportunity->location,
                    'volunteer_role' => $opportunity->volunteer_role,
                    'volunteering_hours' => $opportunity->volunteering_hours,
                ];
            })
        ]);
    }

    /**
     * Admin: Create a new opportunity
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can create opportunities'], 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'discription' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
            'opportunity_coordinatior_name' => ['nullable', 'string', 'max:255'],
            'opportunity_coordinatior_phone' => ['nullable', 'string', 'max:20'],
            'opportunity_coordinatior_email' => ['nullable', 'email', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'volunteer_role' => ['nullable', 'string', 'max:255'],
            'volunteering_hours' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            // Handle image file upload
            if ($request->hasFile('image_file')) {
                $imageFile = $request->file('image_file');
                $imagePath = $imageFile->store('opportunities/images', 'public');
                $data['image_file'] = $imagePath;
            }

            // QR attendance and certificate generation are always enabled
            // Generate QR URL for all opportunities
            $data['qr_url'] = $this->generateQRUrl();

            // Set default opportunity status
            $data['opportunity_status'] = 'active';

            $opportunity = Opportunity::create($data);

            DB::commit();

            return response()->json([
                'message' => 'Opportunity created successfully',
                'opportunity' => $this->formatOpportunityResponse($opportunity->load('opportunityApplications'))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create opportunity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get specific opportunity by ID
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view opportunity details'], 403);
        }

        $opportunity = Opportunity::with(['opportunityApplications'])
            ->withCount('opportunityApplications')
            ->find($id);

        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        // Backfill QR token if missing (QR attendance is always enabled)
        if (!$opportunity->qr_url) {
            $opportunity->qr_url = $this->generateQRUrl();
            $opportunity->save();
        }

        return response()->json([
            'opportunity' => [
                'opportunity_id' => $opportunity->opportunity_id,
                'discription' => $opportunity->discription,
                'title' => $opportunity->title,
                'date' => $opportunity->date,
                'category' => $opportunity->category,
                'country' => $opportunity->country,
                'status' => $opportunity->opportunity_status,
                'location' => $opportunity->location,
                'invitations_count' => $opportunity->opportunity_applications_count,
                'enable_qr_attendance' => true,
                'generate_certificates' => true,
                'opportunity_coordinatior_name' => $opportunity->opportunity_coordinatior_name,
                'opportunity_coordinatior_phone' => $opportunity->opportunity_coordinatior_phone,
                'opportunity_coordinatior_email' => $opportunity->opportunity_coordinatior_email,
                'start_date' => $opportunity->start_date,
                'end_date' => $opportunity->end_date,
                'volunteer_role' => $opportunity->volunteer_role,
                'volunteering_hours' => $opportunity->volunteering_hours,
                'image_file' => $opportunity->image_file,
                'image_url' => $opportunity->image_file ? asset('storage/' . $opportunity->image_file) : null,
                'qr_url' => $opportunity->qr_url,
                'applications' => $opportunity->opportunityApplications->map(function ($application) {
                    return [
                        'application_id' => $application->application_opportunity_id,
                        'student_id' => $application->student_id,
                        'status' => $application->application_status
                    ];
                })
            ]
        ]);
    }

    /**
     * Admin: Update opportunity
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can update opportunities'], 403);
        }

        $opportunity = Opportunity::find($id);

        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        // Simplified validation for JSON requests only
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'discription' => ['sometimes', 'string'],
            'date' => ['sometimes', 'date'],
            'location' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:255'],
            'opportunity_coordinatior_name' => ['sometimes', 'string', 'max:255'],
            'opportunity_coordinatior_phone' => ['sometimes', 'string', 'max:20'],
            'opportunity_coordinatior_email' => ['sometimes', 'email', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'opportunity_status' => ['sometimes', 'in:active,inactive,completed,cancelled'],
            'volunteer_role' => ['sometimes', 'string', 'max:255'],
            'volunteering_hours' => ['sometimes', 'integer', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            // Debug logging
            Log::info('Update Request Data:', $data);
            Log::info('Request All Data:', $request->all());

            // Handle opportunity_status updates
            if (isset($data['opportunity_status'])) {
                $validStatuses = ['active', 'inactive', 'completed', 'cancelled'];
                if (!in_array($data['opportunity_status'], $validStatuses)) {
                    unset($data['opportunity_status']);
                }
            }

            // Generate QR URL if it doesn't exist (QR attendance is always enabled)
            if (!$opportunity->qr_url) {
                $data['qr_url'] = $this->generateQRUrl();
            }

            // Debug: Log what data we're about to update
            Log::debug('Data being sent to update:', $data);

            // Update the opportunity
            $opportunity->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Opportunity updated successfully',
                'opportunity' => $this->formatOpportunityResponse($opportunity->fresh()->load('opportunityApplications'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Failed to update opportunity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Delete opportunity
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can delete opportunities'], 403);
        }

        $opportunity = Opportunity::find($id);

        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        try {
            // Delete associated image file if exists
            if ($opportunity->image_file && Storage::disk('public')->exists($opportunity->image_file)) {
                Storage::disk('public')->delete($opportunity->image_file);
            }

            $opportunity->delete();

            return response()->json([
                'message' => 'Opportunity deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete opportunity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Change opportunity status
     */
    public function changeStatus(Request $request, $id)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can change opportunity status'], 403);
        }

        $opportunity = Opportunity::find($id);

        if (!$opportunity) {
            return response()->json(['message' => 'Opportunity not found'], 404);
        }

        $data = $request->validate([
            'opportunity_status' => ['required', 'in:active,inactive,completed,cancelled']
        ]);

        try {
            $opportunity->update(['opportunity_status' => $data['opportunity_status']]);

            return response()->json([
                'message' => 'Opportunity status updated successfully',
                'opportunity' => $this->formatOpportunityResponse($opportunity)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update opportunity status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Get opportunity statistics
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can view opportunity statistics'], 403);
        }

        $statistics = [
            'total_opportunities' => Opportunity::count(),
            'active_opportunities' => Opportunity::where('opportunity_status', 'active')->count(),
            'completed_opportunities' => Opportunity::where('opportunity_status', 'completed')->count(),
            'cancelled_opportunities' => Opportunity::where('opportunity_status', 'cancelled')->count(),
            'total_applications' => DB::table('application_opportunities')->count(),
            'pending_applications' => DB::table('application_opportunities')->where('application_status', 'pending')->count(),
            'approved_applications' => DB::table('application_opportunities')->where('application_status', 'approved')->count(),
        ];

        return response()->json([
            'statistics' => $statistics
        ]);
    }

    /**
     * Generate a unique QR URL for the opportunity
     */
    private function generateQRUrl()
    {
        $token = Str::random(32);
        return $token;
    }

    /**
     * Public: QR Code scanning endpoint (no authentication required)
     * Returns opportunity information for QR scan
     * Only works when opportunity status is "active"
     */
    public function qrScan(Request $request, $token)
    {
        // Find opportunity by QR token
        $opportunity = Opportunity::where('qr_url', $token)->first();

        if (!$opportunity) {
            return response()->json(['message' => 'Invalid QR code'], 404);
        }

        // Check if opportunity is active
        if ($opportunity->opportunity_status !== 'active') {
            return response()->json([
                'message' => 'QR code scanning is not available',
                'reason' => 'Opportunity is not active',
                'opportunity_status' => $opportunity->opportunity_status,
                'available_when' => 'Opportunity status is "active"'
            ], 403);
        }

        // QR attendance is always enabled

        // Return opportunity info for QR scan
        return response()->json([
            'message' => 'QR code scanned successfully',
            'opportunity' => [
                'opportunity_id' => $opportunity->opportunity_id,
                'title' => $opportunity->title,
                'description' => $opportunity->discription,
                'date' => $opportunity->date,
                'location' => $opportunity->location,
                'country' => $opportunity->country,
                'category' => $opportunity->category,
                'opportunity_status' => $opportunity->opportunity_status,
                'qr_token' => $token,
                'enable_qr_attendance' => true,
                'generate_certificates' => true,
                'coordinator_name' => $opportunity->opportunity_coordinatior_name,
                'coordinator_phone' => $opportunity->opportunity_coordinatior_phone,
                'coordinator_email' => $opportunity->opportunity_coordinatior_email,
                'volunteer_role' => $opportunity->volunteer_role,
                'volunteering_hours' => $opportunity->volunteering_hours,
                'image_file' => $opportunity->image_file,
                'image_url' => $opportunity->image_file ? asset('storage/' . $opportunity->image_file) : null,
            ]
        ]);
    }

    /**
     * Format opportunity response to ensure proper boolean values
     */
    private function formatOpportunityResponse($opportunity)
    {
        $opportunityArray = $opportunity->toArray();

        // QR attendance and certificate generation are always enabled
        $opportunityArray['enable_qr_attendance'] = true;
        $opportunityArray['generate_certificates'] = true;

        // Add image URL if image exists
        if ($opportunity->image_file) {
            $opportunityArray['image_url'] = $opportunity->image_url;
        }

        return $opportunityArray;
    }
}
