<?php

namespace App\Http\Controllers;

use App\Models\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $universities = University::with('country')->get();
        return response()->json($universities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'university_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'exists:countries,country_id'],
            'is_active' => ['boolean'],
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
    public function show(University $university)
    {
        $university->load('country');
        return response()->json($university);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, University $university)
    {
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
    public function destroy(University $university)
    {
        $university->delete();
        return response()->json(['message' => 'University deleted successfully']);
    }
}
