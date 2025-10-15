<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UniversityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Check if user is admin
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        $query = University::with('country');

        // Admin sees ALL universities (active + inactive)
        // Non-admin users only see active universities
        if (!$isAdmin) {
            $query->where('is_active', true);
        }

        $universities = $query->get();

        return response()->json($universities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user is admin
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
            return response()->json(['message' => 'Only admins can create universities'], 403);
        }

        $data = $request->validate([
            'university_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,country_id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $university = University::create($data);
        $university->load('country');

        return response()->json([
            'message' => 'University created successfully',
            'university' => $university,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, University $university)
    {
        $user = $request->user();
        
        // Check if user is admin
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        // Non-admin users cannot see inactive universities
        if (!$university->is_active && !$isAdmin) {
            return response()->json(['message' => 'University not available'], 404);
        }

        $university->load('country');
        return response()->json($university);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, University $university)
    {
        // Check if user is admin
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
            return response()->json(['message' => 'Only admins can update universities'], 403);
        }

        $data = $request->validate([
            'university_name' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'country_id' => ['sometimes', 'exists:countries,country_id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $university->update($data);
        $university->load('country');

        return response()->json([
            'message' => 'University updated successfully',
            'university' => $university,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, University $university)
    {
        // Check if user is admin
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
            return response()->json(['message' => 'Only admins can delete universities'], 403);
        }

        $university->delete();
        return response()->json(['message' => 'University deleted successfully']);
    }
}