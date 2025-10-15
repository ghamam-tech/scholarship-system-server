<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
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

        $query = Country::query();

        // Admin sees ALL countries (active + inactive) in the listing
        // Non-admin users only see active countries in the listing
        if (!$isAdmin) {
            $query->where('is_active', true);
        }

        $countries = $query->get();

        return response()->json($countries);
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
            return response()->json(['message' => 'Only admins can create countries'], 403);
        }

        $data = $request->validate([
            'country_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'max:3', 'unique:countries,country_code'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $country = Country::create($data);

        return response()->json([
            'message' => 'Country created successfully',
            'country' => $country,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Country $country)
    {
        // For show method: Everyone can view any country (active or inactive)
        // No restrictions - if someone has the direct link, they can see it
        $country->load('universities');
        return response()->json($country);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Country $country)
    {
        // Check if user is admin
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
            return response()->json(['message' => 'Only admins can update countries'], 403);
        }

        $data = $request->validate([
            'country_name' => ['sometimes', 'string', 'max:255'],
            'country_code' => ['sometimes', 'string', 'max:3', 'unique:countries,country_code,' . $country->country_id . ',country_id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $country->update($data);

        return response()->json([
            'message' => 'Country updated successfully',
            'country' => $country,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Country $country)
    {
        // Check if user is admin
        $user = $request->user();
        $isAdmin = $user && (
            ($user->role instanceof UserRole && $user->role === UserRole::ADMIN) ||
            ($user->role === UserRole::ADMIN->value)
        );

        if (!$isAdmin) {
            return response()->json(['message' => 'Only admins can delete countries'], 403);
        }

        $country->delete();
        return response()->json(['message' => 'Country deleted successfully']);
    }
}