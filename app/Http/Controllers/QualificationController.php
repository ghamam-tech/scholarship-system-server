<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class QualificationController extends Controller
{
    /**
     * Get applicant's qualifications
     */
    public function index(Request $request)
    {
        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $qualifications = $applicant->qualifications()->get();
        return response()->json($qualifications);
    }

    /**
     * Add qualification to applicant
     */
    public function store(Request $request)
    {
        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

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
            $filename = time() . '_' . str_replace(' ', '_', $request->file('document_file')->getClientOriginalName());
            $documentPath = $request->file('document_file')->storeAs("applicants/{$applicant->applicant_id}/qualifications", $filename, 's3');
            $documentFile = config('filesystems.disks.s3.url') . '/' . $documentPath;

            $qualification = Qualification::create([
                'applicant_id' => $applicant->applicant_id,
                'qualification_type' => $data['qualification_type'],
                'institute_name' => $data['institute_name'],
                'year_of_graduation' => $data['year_of_graduation'],
                'cgpa' => $data['cgpa'] ?? null,
                'cgpa_out_of' => $data['cgpa_out_of'] ?? null,
                'language_of_study' => $data['language_of_study'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'research_title' => $data['research_title'] ?? null,
                'document_file' => $documentFile,
            ]);

            return response()->json([
                'message' => 'Qualification added successfully',
                'qualification' => $qualification
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add qualification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update qualification
     */
    public function update(Request $request, $qualificationId)
    {
        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $qualification = $applicant->qualifications()->findOrFail($qualificationId);

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
            $documentFile = $qualification->document_file;
            if ($request->hasFile('document_file')) {
                if ($documentFile) {
                    Storage::disk('s3')->delete($documentFile);
                }
                $filename = time() . '_' . str_replace(' ', '_', $request->file('document_file')->getClientOriginalName());
                $documentPath = $request->file('document_file')->storeAs("applicants/{$applicant->applicant_id}/qualifications", $filename, 's3');
                $documentFile = config('filesystems.disks.s3.url') . '/' . $documentPath;
            }

            $qualification->update([
                'qualification_type' => $data['qualification_type'] ?? $qualification->qualification_type,
                'institute_name' => $data['institute_name'] ?? $qualification->institute_name,
                'year_of_graduation' => $data['year_of_graduation'] ?? $qualification->year_of_graduation,
                'cgpa' => $data['cgpa'] ?? $qualification->cgpa,
                'cgpa_out_of' => $data['cgpa_out_of'] ?? $qualification->cgpa_out_of,
                'language_of_study' => $data['language_of_study'] ?? $qualification->language_of_study,
                'specialization' => $data['specialization'] ?? $qualification->specialization,
                'research_title' => $data['research_title'] ?? $qualification->research_title,
                'document_file' => $documentFile,
            ]);

            return response()->json([
                'message' => 'Qualification updated successfully',
                'qualification' => $qualification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update qualification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete qualification
     */
    public function destroy(Request $request, $qualificationId)
    {
        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $qualification = $applicant->qualifications()->findOrFail($qualificationId);

        try {
            if ($qualification->document_file) {
                Storage::disk('s3')->delete($qualification->document_file);
            }

            $qualification->delete();

            return response()->json([
                'message' => 'Qualification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete qualification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
