<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sponsor;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class SponsorController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Determine user role with proper enum handling
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );
        
        $isSponsor = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::SPONSOR) ||
            ($user->role === UserRole::SPONSOR->value)
        );

        $q = Sponsor::with('user');

        if ($isAdmin) {
            // Admin sees ALL sponsors (both active and inactive)
            // No filter applied
        } elseif ($isSponsor) {
            // Sponsors can only see themselves
            $sponsor = Sponsor::where('user_id', $user->user_id)->first();
            if ($sponsor) {
                $q->where('sponsor_id', $sponsor->sponsor_id);
            } else {
                // If sponsor doesn't have a sponsor profile, return empty
                return response()->json([]);
            }
        } else {
            // Public users (including applicants) only see active sponsors
            $q->where('is_active', true);
        }

        return response()->json($q->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required','string','max:255'],
            'country'   => ['nullable','string','max:100'],
            'is_active' => ['sometimes','boolean'],
            'email'     => ['required','email','max:255','unique:users,email'],
            'password'  => ['nullable', Password::min(8)],
        ]);

        $result = DB::transaction(function () use ($data) {
            $user = User::create([
                'email'    => $data['email'],
                'password' => $data['password'] ?? Str::random(12),
                'role'     => UserRole::SPONSOR->value,
            ]);

            $sponsor = Sponsor::create([
                'user_id'   => $user->user_id,
                'name'      => $data['name'],
                'country'   => $data['country'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return compact('user','sponsor');
        });

        return response()->json([
            'message' => 'Sponsor created successfully.',
            'user'    => $result['user']->only(['user_id','email','role','created_at','updated_at']),
            'sponsor' => $result['sponsor'],
        ], 201);
    }

    public function show(Request $request, Sponsor $sponsor)
    {
        $user = $request->user();
        
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );
        
        $isSponsor = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::SPONSOR) ||
            ($user->role === UserRole::SPONSOR->value)
        );

        // Check if sponsor is trying to access another sponsor
        if ($isSponsor && $sponsor->user_id !== $user->user_id) {
            return response()->json(['message' => 'You can only view your own sponsor profile'], 403);
        }

        // Check if non-admin/non-owner is trying to view inactive sponsor
        if (!$sponsor->is_active && !$isAdmin && (!$isSponsor || $sponsor->user_id !== $user->user_id)) {
            return response()->json(['message' => 'This sponsor is not available'], 403);
        }

        return response()->json($sponsor->load('user'));
    }

    public function update(Request $request, Sponsor $sponsor)
    {
        $user = $request->user();
        
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );
        
        $isSponsor = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::SPONSOR) ||
            ($user->role === UserRole::SPONSOR->value)
        );

        // Sponsors can only update their own profile
        if ($isSponsor && $sponsor->user_id !== $user->user_id) {
            return response()->json(['message' => 'You can only update your own sponsor profile'], 403);
        }

        $data = $request->validate([
            'name'       => ['sometimes','string','max:255'],
            'country'    => ['sometimes','nullable','string','max:100'],
            'is_active'  => ['sometimes','boolean'], // Only admin should use this

            'email'      => ['sometimes','email','max:255',
                Rule::unique('users','email')->ignore($sponsor->user_id,'user_id')
            ],
            'password'   => ['sometimes','nullable', Password::min(8)],
        ]);

        // Sponsors cannot change is_active
        if ($isSponsor && $request->has('is_active')) {
            return response()->json(['message' => 'You cannot change your active status'], 403);
        }

        // Only apply fields that were sent
        $sponsorFields = [];
        if ($request->has('name'))      $sponsorFields['name'] = $data['name'];
        if ($request->has('country'))   $sponsorFields['country'] = $data['country'] ?? null;
        if ($request->has('is_active') && $isAdmin) $sponsorFields['is_active'] = (bool) $data['is_active'];

        if ($sponsorFields) {
            $sponsor->update($sponsorFields);
        }

        $userFields = [];
        if ($request->has('email'))     $userFields['email'] = $data['email'];
        if ($request->has('password') && !empty($data['password'])) {
            $userFields['password'] = $data['password'];
        }
        if ($userFields) {
            $sponsor->user()->update($userFields);
        }

        return response()->json([
            'message' => 'Sponsor updated successfully',
            'sponsor' => $sponsor->load('user:user_id,email,role'),
        ]);
    }

    public function destroy(Sponsor $sponsor)
    {
        // Only admin can delete sponsors
        $user = Auth::user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
            return response()->json(['message' => 'Only admins can delete sponsors'], 403);
        }

        $sponsor->delete();
        return response()->json(['message' => 'Sponsor deleted successfully.']);
    }
}