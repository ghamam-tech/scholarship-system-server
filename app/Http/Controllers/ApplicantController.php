<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $applicants = Applicant::with('user')->get();
        return response()->json($applicants);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // User credentials
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            
            // Applicant personal information
            'ar_name' => ['required', 'string', 'max:255'],
            'en_name' => ['required', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'parent_contact_name' => ['nullable', 'string', 'max:255'],
            'parent_contact_phone' => ['nullable', 'string', 'max:20'],
            'residence_country' => ['nullable', 'string', 'max:100'],
            'passport_copy_url' => ['nullable', 'string', 'max:500'],
            'volunteering_certificate_url' => ['nullable', 'string', 'max:500'],
            'language' => ['nullable', 'string', 'max:50'],
            'is_studied_in_saudi' => ['nullable', 'boolean'],
        ]);

        // Create applicant user (role=applicant)
        $user = User::create([
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::APPLICANT->value,
        ]);

        // Create applicant profile
        $applicant = Applicant::create([
            'user_id' => $user->user_id,
            'ar_name' => $data['ar_name'],
            'en_name' => $data['en_name'],
            'nationality' => $data['nationality'] ?? null,
            'gender' => $data['gender'] ?? null,
            'place_of_birth' => $data['place_of_birth'] ?? null,
            'phone' => $data['phone'] ?? null,
            'passport_number' => $data['passport_number'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'parent_contact_name' => $data['parent_contact_name'] ?? null,
            'parent_contact_phone' => $data['parent_contact_phone'] ?? null,
            'residence_country' => $data['residence_country'] ?? null,
            'passport_copy_url' => $data['passport_copy_url'] ?? null,
            'volunteering_certificate_url' => $data['volunteering_certificate_url'] ?? null,
            'language' => $data['language'] ?? null,
            'is_studied_in_saudi' => $data['is_studied_in_saudi'] ?? false,
        ]);

        return response()->json([
            'message' => 'Applicant created successfully',
            'user' => $user,
            'applicant' => $applicant,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Applicant $applicant)
    {
        $applicant->load('user');
        return response()->json($applicant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Applicant $applicant)
    {
        $data = $request->validate([
            // User credentials
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $applicant->user_id . ',user_id'],
            'password' => ['sometimes', 'nullable', Password::min(8)],
            
            // Applicant personal information
            'ar_name' => ['sometimes', 'string', 'max:255'],
            'en_name' => ['sometimes', 'string', 'max:255'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:100'],
            'gender' => ['sometimes', 'nullable', 'string', 'in:male,female'],
            'place_of_birth' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'passport_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'parent_contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'parent_contact_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'residence_country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'passport_copy_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'volunteering_certificate_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'language' => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_studied_in_saudi' => ['sometimes', 'nullable', 'boolean'],
        ]);

        // Update user if email or password provided
        if (isset($data['email']) || isset($data['password'])) {
            $userData = [];
            if (isset($data['email'])) {
                $userData['email'] = $data['email'];
            }
            if (isset($data['password'])) {
                $userData['password'] = $data['password'];
            }
            $applicant->user->update($userData);
        }

        // Update applicant profile
        $applicantData = collect($data)->except(['email', 'password'])->toArray();
        if (!empty($applicantData)) {
            $applicant->update($applicantData);
        }

        $applicant->load('user');
        return response()->json([
            'message' => 'Applicant updated successfully',
            'applicant' => $applicant,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Applicant $applicant)
    {
        $applicant->user->delete(); // This will cascade delete the applicant due to foreign key constraint
        return response()->json(['message' => 'Applicant deleted successfully']);
    }

    /**
     * Public registration endpoint (no authentication required)
     */
    public function register(Request $request)
    {
        return $this->store($request);
    }
}
