<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $countries = Country::all();
        return response()->json($countries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'country_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'max:3', 'unique:countries,country_code'],
            'is_active' => ['boolean'],
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
    public function show(Country $country)
    {
        $country->load('universities');
        return response()->json($country);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Country $country)
    {
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
    public function destroy(Country $country)
    {
        $country->delete();
        return response()->json(['message' => 'Country deleted successfully']);
    }
}
