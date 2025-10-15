<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\Country;
use App\Models\University;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScholarshipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        $query = Scholarship::with(['sponsor', 'countries', 'universities']);

        if (!$isAdmin) {
            $query->where('is_active', true)
                  ->where('is_hided', false);
        }

        $scholarships = $query->get();
        return response()->json($scholarships);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
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

        // Validate that selected universities belong to selected countries
        if ($request->has('university_ids') && !empty($data['university_ids'])) {
            $validUniversities = University::whereIn('country_id', $data['country_ids'])
                ->whereIn('university_id', $data['university_ids'])
                ->pluck('university_id')
                ->toArray();

            if (count($data['university_ids']) !== count($validUniversities)) {
                return response()->json([
                    'message' => 'Some selected universities do not belong to the selected countries',
                    'errors' => [
                        'university_ids' => ['Invalid university selection for the chosen countries']
                    ]
                ], 422);
            }
        }

        $scholarship = Scholarship::create($data);
        
        // Attach countries to scholarship
        $scholarship->countries()->attach($data['country_ids']);

        // Attach universities to scholarship if provided
        if ($request->has('university_ids') && !empty($data['university_ids'])) {
            $scholarship->universities()->attach($data['university_ids']);
        }

        $scholarship->load(['sponsor', 'countries', 'universities']);

        return response()->json([
            'message' => 'Scholarship created successfully',
            'scholarship' => $scholarship,
        ], 201);
    }

    public function show(Request $request, Scholarship $scholarship)
    {
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if ((!$scholarship->is_active || $scholarship->is_hided) && !$isAdmin) {
            return response()->json(['message' => 'Scholarship not available'], 404);
        }

        $scholarship->load(['sponsor', 'countries', 'universities']);
        return response()->json($scholarship);
    }

    public function update(Request $request, Scholarship $scholarship)
    {
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
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

        // Validate that selected universities belong to selected countries
        if ($request->has('university_ids') && $request->has('country_ids') && !empty($data['university_ids'])) {
            $validUniversities = University::whereIn('country_id', $data['country_ids'])
                ->whereIn('university_id', $data['university_ids'])
                ->pluck('university_id')
                ->toArray();

            if (count($data['university_ids']) !== count($validUniversities)) {
                return response()->json([
                    'message' => 'Some selected universities do not belong to the selected countries',
                    'errors' => [
                        'university_ids' => ['Invalid university selection for the chosen countries']
                    ]
                ], 422);
            }
        }

        $scholarship->update($data);

        // Update countries if provided
        if ($request->has('country_ids')) {
            $scholarship->countries()->sync($data['country_ids']);
        }

        // Update universities if provided
        if ($request->has('university_ids')) {
            $scholarship->universities()->sync($data['university_ids']);
        }

        $scholarship->load(['sponsor', 'countries', 'universities']);

        return response()->json([
            'message' => 'Scholarship updated successfully',
            'scholarship' => $scholarship,
        ]);
    }

    public function destroy(Request $request, Scholarship $scholarship)
    {
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
            return response()->json(['message' => 'Only admins can delete scholarships'], 403);
        }

        $scholarship->delete();
        return response()->json(['message' => 'Scholarship deleted successfully']);
    }

    /**
     * Get universities by country IDs (for frontend dropdown)
     */
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

        return response()->json($universities);
    }
}