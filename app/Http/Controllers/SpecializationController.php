<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specializations = Specialization::all();
        return response()->json($specializations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'specialization_name' => ['required', 'string', 'max:255'],
            'faculty_name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $specialization = Specialization::create($data);

        return response()->json([
            'message' => 'Specialization created successfully',
            'specialization' => $specialization,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Specialization $specialization)
    {
        return response()->json($specialization);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Specialization $specialization)
    {
        $data = $request->validate([
            'specialization_name' => ['sometimes', 'string', 'max:255'],
            'faculty_name' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $specialization->update($data);

        return response()->json([
            'message' => 'Specialization updated successfully',
            'specialization' => $specialization,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specialization $specialization)
    {
        $specialization->delete();
        return response()->json(['message' => 'Specialization deleted successfully']);
    }
}
