<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $scholarships = Scholarship::all();
        return response()->json($scholarships);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'scholarship_name' => ['required', 'string', 'max:255'],
            'scholarship_type' => ['required', 'string', 'max:255'],
            'allowed_program' => ['required', 'string', 'max:255'],
            'total_beneficiaries' => ['required', 'integer', 'min:1'],
            'opening_date' => ['required', 'date'],
            'closing_date' => ['required', 'date', 'after:opening_date'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_hidden' => ['boolean'],
        ]);

        $scholarship = Scholarship::create($data);

        return response()->json([
            'message' => 'Scholarship created successfully',
            'scholarship' => $scholarship,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Scholarship $scholarship)
    {
        return response()->json($scholarship);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Scholarship $scholarship)
    {
        $data = $request->validate([
            'scholarship_name' => ['sometimes', 'string', 'max:255'],
            'scholarship_type' => ['sometimes', 'string', 'max:255'],
            'allowed_program' => ['sometimes', 'string', 'max:255'],
            'total_beneficiaries' => ['sometimes', 'integer', 'min:1'],
            'opening_date' => ['sometimes', 'date'],
            'closing_date' => ['sometimes', 'date', 'after:opening_date'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'is_hidden' => ['sometimes', 'boolean'],
        ]);

        $scholarship->update($data);

        return response()->json([
            'message' => 'Scholarship updated successfully',
            'scholarship' => $scholarship,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scholarship $scholarship)
    {
        $scholarship->delete();
        return response()->json(['message' => 'Scholarship deleted successfully']);
    }
}
