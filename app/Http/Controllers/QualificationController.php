<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class QualificationController extends Controller
{
    /**
     * List the authenticated user's qualifications
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $qualifications = $user->qualifications()->get();

        // Attach a public URL (without storing it in DB)
        $qualifications->transform(function ($q) {
            $q->document_url = $q->document_file
                ? Storage::disk('s3')->url($q->document_file)
                : null;
            return $q;
        });

        return response()->json($qualifications);
    }

    /**
     * Create a qualification for the authenticated user
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'qualification_type' => ['required', Rule::in(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other'])],
            'institute_name' => ['required', 'string', 'max:255'],
            'year_of_graduation' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'cgpa' => ['nullable', 'numeric', 'min:0'],
            'cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
            'language_of_study' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'research_title' => ['nullable', 'string', 'max:500'],
            'document_file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            // Store file under a user-scoped prefix; keep ONLY the path in DB
            $filename = time() . '_' . str_replace(' ', '_', $request->file('document_file')->getClientOriginalName());
            $documentPath = $request->file('document_file')->storeAs(
                "users/{$user->user_id}/qualifications",
                $filename,
                's3'
            );

            $qualification = Qualification::create([
                'user_id' => $user->user_id,
                'qualification_type' => $data['qualification_type'],
                'institute_name' => $data['institute_name'],
                'year_of_graduation' => $data['year_of_graduation'],
                'cgpa' => $data['cgpa'] ?? null,
                'cgpa_out_of' => $data['cgpa_out_of'] ?? null,
                'language_of_study' => $data['language_of_study'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'research_title' => $data['research_title'] ?? null,
                'document_file' => $documentPath, // store path, not URL
            ]);

            // Add a public URL in the response only
            $qualification->document_url = Storage::disk('s3')->url($qualification->document_file);

            return response()->json([
                'message' => 'Qualification added successfully',
                'qualification' => $qualification,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add qualification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a qualification owned by the authenticated user
     */
    public function update(Request $request, $qualificationId)
    {
        $user = $request->user();

        // Ensure the qualification belongs to this user
        $qualification = $user->qualifications()->findOrFail($qualificationId);

        $data = $request->validate([
            'qualification_type' => ['sometimes', Rule::in(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other'])],
            'institute_name' => ['sometimes', 'string', 'max:255'],
            'year_of_graduation' => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'cgpa' => ['nullable', 'numeric', 'min:0'],
            'cgpa_out_of' => ['nullable', 'numeric', 'min:0'],
            'language_of_study' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'research_title' => ['nullable', 'string', 'max:500'],
            'document_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            $documentPath = $qualification->document_file;

            if ($request->hasFile('document_file')) {
                // Delete old file using its PATH
                if ($documentPath) {
                    Storage::disk('s3')->delete($documentPath);
                }

                $filename = time() . '_' . str_replace(' ', '_', $request->file('document_file')->getClientOriginalName());
                $documentPath = $request->file('document_file')->storeAs(
                    "users/{$user->user_id}/qualifications",
                    $filename,
                    's3'
                );
            }

            $qualification->update(array_filter([
                'qualification_type' => $data['qualification_type'] ?? null,
                'institute_name' => $data['institute_name'] ?? null,
                'year_of_graduation' => $data['year_of_graduation'] ?? null,
                'cgpa' => array_key_exists('cgpa', $data) ? $data['cgpa'] : null,
                'cgpa_out_of' => array_key_exists('cgpa_out_of', $data) ? $data['cgpa_out_of'] : null,
                'language_of_study' => $data['language_of_study'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'research_title' => $data['research_title'] ?? null,
                'document_file' => $documentPath,
            ], static fn($v) => $v !== null));

            $qualification->document_url = $qualification->document_file
                ? Storage::disk('s3')->url($qualification->document_file)
                : null;

            return response()->json([
                'message' => 'Qualification updated successfully',
                'qualification' => $qualification,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update qualification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a qualification owned by the authenticated user
     */
    public function destroy(Request $request, $qualificationId)
    {
        $user = $request->user();

        $qualification = $user->qualifications()->findOrFail($qualificationId);

        try {
            if ($qualification->document_file) {
                Storage::disk('s3')->delete($qualification->document_file); // delete by PATH
            }

            $qualification->delete();

            return response()->json(['message' => 'Qualification deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete qualification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
