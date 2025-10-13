<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of sponsors (admin only)
     */
    public function index()
    {
        $sponsors = Sponsor::with('user')->get();
        return response()->json($sponsors);
    }

    /**
     * Store a newly created sponsor (admin only)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', Password::min(8)],
        ]);

        // Create sponsor user (role=sponsor)
        $user = User::create([
            'email' => $data['email'],
            'password' => $data['password'] ?? Str::random(12),
            'role' => UserRole::SPONSOR->value,
        ]);

        // Create sponsor profile
        $sponsor = Sponsor::create([
            'user_id' => $user->user_id,
            'name' => $data['name'],
            'country' => $data['country'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Sponsor created successfully',
            'user' => $user,
            'sponsor' => $sponsor,
        ], 201);
    }

    /**
     * Display the specified sponsor
     */
    public function show(Sponsor $sponsor)
    {
        $sponsor->load('user');
        return response()->json($sponsor);
    }

    /**
     * Update the specified sponsor
     */
    public function update(Request $request, Sponsor $sponsor)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $sponsor->user_id . ',user_id'],
            'password' => ['sometimes', 'nullable', Password::min(8)],
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
            $sponsor->user->update($userData);
        }

        // Update sponsor profile
        $sponsorData = collect($data)->except(['email', 'password'])->toArray();
        if (!empty($sponsorData)) {
            $sponsor->update($sponsorData);
        }

        $sponsor->load('user');
        return response()->json([
            'message' => 'Sponsor updated successfully',
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Remove the specified sponsor
     */
    public function destroy(Sponsor $sponsor)
    {
        $sponsor->user->delete(); // This will cascade delete the sponsor due to foreign key constraint
        return response()->json(['message' => 'Sponsor deleted successfully']);
    }

    /**
     * Get authenticated user's profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load(['admin', 'applicant', 'sponsor']);
        return response()->json($user);
    }

    /**
     * Update authenticated user's profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->user_id . ',user_id'],
            'password' => ['sometimes', 'nullable', Password::min(8)],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Legacy method for backward compatibility
     */
    public function createSponsor(Request $request)
    {
        return $this->store($request);
    }
}
