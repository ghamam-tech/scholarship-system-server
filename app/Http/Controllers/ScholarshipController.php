<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\Country;
use App\Models\University;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScholarshipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        $isAdmin = $user && $user->role === UserRole::ADMIN;

        $query = Scholarship::with(['sponsor', 'countries', 'universities']);

        if (!$isAdmin) {
            // Non-admin users (including unauthorized users) only see active, non-hidden scholarships
            $query->where('is_active', true)
                ->where('is_hided', false)
                ->where('closing_date', '>', now()); // Only show scholarships that haven't closed yet
        }
        // Admin users: NO filters applied - they see ALL scholarships

        $scholarships = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $scholarships,
            'meta' => [
                'total' => $scholarships->count(),
                'user_role' => $user ? $user->role->value : 'guest',
                'is_admin' => $isAdmin,
                'user_id' => $user ? $user->user_id : null,
                'filters_applied' => !$isAdmin ? ['is_active=true', 'is_hided=false', 'closing_date>now'] : []
            ]
        ]);
    }

    public function show(Request $request, Scholarship $scholarship)
    {
        $user = $request->user();

        // Check if user is admin
        $isAdmin = $user && $user->role === UserRole::ADMIN;

        // Admin can see ANY scholarship, non-admin only active & non-hidden & not closed
        if (!$isAdmin && (!$scholarship->is_active || $scholarship->is_hided || $scholarship->closing_date <= now())) {
            return response()->json(['message' => 'Scholarship not available'], 404);
        }

        $scholarship->load(['sponsor', 'countries', 'universities']);
        return response()->json([
            'data' => $scholarship,
            'meta' => [
                'user_role' => $user ? $user->role->value : 'guest',
                'is_admin' => $isAdmin,
                'user_id' => $user ? $user->user_id : null
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user is admin
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can create scholarships'], 403);
        }

        $data = $request->validate([
            'scholarship_name' => ['required', 'string', 'max:255'],
            'scholarship_type' => ['nullable', 'string', 'max:255'],
            'allowed_program' => ['nullable', 'string', 'max:255'],
            'total_beneficiaries' => ['nullable', 'integer', 'min:0'],
            'opening_date' => ['nullable', 'date'],
            'closing_date' => ['nullable', 'date', 'after_or_equal:opening_date'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'is_hided' => ['sometimes', 'boolean'],
            'sponsor_id' => ['required', 'exists:sponsors,sponsor_id'],
            'country_ids' => ['required', 'array', 'min:1'],
            'country_ids.*' => ['exists:countries,country_id'],
            'university_ids' => ['sometimes', 'array'],
            'university_ids.*' => ['exists:universities,university_id']
        ]);

        // Validate university-country relationships
        if ($request->has('university_ids') && !empty($data['university_ids'])) {
            $validUniversities = University::whereIn('country_id', $data['country_ids'])
                ->whereIn('university_id', $data['university_ids'])
                ->pluck('university_id')
                ->toArray();

            if (count($data['university_ids']) !== count($validUniversities)) {
                return response()->json([
                    'message' => 'Some selected universities do not belong to the selected countries'
                ], 422);
            }
        }

        $scholarship = Scholarship::create($data);

        // Attach relationships
        $scholarship->countries()->attach($data['country_ids']);

        if ($request->has('university_ids') && !empty($data['university_ids'])) {
            $scholarship->universities()->attach($data['university_ids']);
        }

        $scholarship->load(['sponsor', 'countries', 'universities']);

        return response()->json([
            'message' => 'Scholarship created successfully',
            'data' => $scholarship,
        ], 201);
    }

    public function update(Request $request, Scholarship $scholarship)
    {
        $user = $request->user();

        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can update scholarships'], 403);
        }

        $data = $request->validate([
            'scholarship_name' => ['sometimes', 'string', 'max:255'],
            'scholarship_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'allowed_program' => ['sometimes', 'nullable', 'string', 'max:255'],
            'total_beneficiaries' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'opening_date' => ['sometimes', 'nullable', 'date'],
            'closing_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:opening_date'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'is_hided' => ['sometimes', 'boolean'],
            'sponsor_id' => ['sometimes', 'exists:sponsors,sponsor_id'],
            'country_ids' => ['sometimes', 'array', 'min:1'],
            'country_ids.*' => ['exists:countries,country_id'],
            'university_ids' => ['sometimes', 'array'],
            'university_ids.*' => ['exists:universities,university_id']
        ]);

        $scholarship->update($data);

        // Update relationships if provided
        if ($request->has('country_ids')) {
            $scholarship->countries()->sync($data['country_ids']);
        }

        if ($request->has('university_ids')) {
            $scholarship->universities()->sync($data['university_ids']);
        }

        $scholarship->load(['sponsor', 'countries', 'universities']);

        return response()->json([
            'message' => 'Scholarship updated successfully',
            'data' => $scholarship,
        ]);
    }

    public function destroy(Request $request, Scholarship $scholarship)
    {
        $user = $request->user();

        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json(['message' => 'Only admins can delete scholarships'], 403);
        }

        $scholarship->delete();
        return response()->json(['message' => 'Scholarship deleted successfully']);
    }

    public function getUniversitiesByCountries(Request $request)
    {
        $data = $request->validate([
            'country_ids' => ['required', 'array', 'min:1'],
            'country_ids.*' => ['exists:countries,country_id']
        ]);

        $universities = University::with('country')
            ->whereIn('country_id', $data['country_ids'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'data' => $universities
        ]);
    }

    // Admin-only methods - show ALL scholarships (including expired/hidden)
    public function adminIndex(Request $request)
    {
        $user = $request->user();

        // This method is only accessible to admins (protected by middleware)
        $scholarships = Scholarship::with(['sponsor', 'countries', 'universities'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $scholarships,
            'meta' => [
                'total' => $scholarships->count(),
                'user_role' => $user->role->value,
                'is_admin' => true,
                'user_id' => $user->user_id,
                'filters_applied' => [], // No filters for admin
                'note' => 'Admin view - shows ALL scholarships including expired and hidden ones'
            ]
        ]);
    }

    public function adminShow(Request $request, Scholarship $scholarship)
    {
        $user = $request->user();

        // Admin can see ANY scholarship
        $scholarship->load(['sponsor', 'countries', 'universities']);

        return response()->json([
            'data' => $scholarship,
            'meta' => [
                'user_role' => $user->role->value,
                'is_admin' => true,
                'user_id' => $user->user_id,
                'note' => 'Admin view - can see any scholarship regardless of status'
            ]
        ]);
    }

    // Debug method for testing user authentication
    public function debugUser(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'role' => $user ? $user->role->value : 'guest',
            'is_admin' => $user && $user->role === UserRole::ADMIN,
            'authenticated' => $user !== null
        ]);
    }
}
