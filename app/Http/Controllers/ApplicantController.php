<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApplicantController extends Controller
{
    /**
     * Display a listing of applicants
     */
    public function index()
    {
        $applicants = Applicant::with('user', 'qualifications', 'applications')->get();
        return response()->json($applicants);
    }

    /**
     * Complete applicant profile
     */
    public function completeProfile(Request $request)
    {
        $applicant = $request->user()->applicant;

        if (!$applicant) {
            return response()->json(['message' => 'Applicant profile not found'], 404);
        }

        $data = $request->validate([
            'ar_name' => ['required', 'string', 'max:255'],
            'en_name' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'string', 'in:male,female'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'passport_number' => ['required', 'string', 'max:50', 'unique:applicants,passport_number,' . $applicant->applicant_id . ',applicant_id'],
            'date_of_birth' => ['required', 'date'],
            'parent_contact_name' => ['required', 'string', 'max:255'],
            'parent_contact_phone' => ['required', 'string', 'max:20'],
            'residence_country' => ['required', 'string', 'max:100'],
            'language' => ['required', 'string', 'max:50'],
            'is_studied_in_saudi' => ['required', 'boolean'],
            'tahseeli_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'qudorat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'passport_copy' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'personal_image' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'secondary_school_certificate' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'secondary_school_transcript' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'volunteering_certificate' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            $this->handleFileUploads($request, $applicant);

            $applicant->update([
                'ar_name' => $data['ar_name'],
                'en_name' => $data['en_name'],
                'nationality' => $data['nationality'],
                'gender' => $data['gender'],
                'place_of_birth' => $data['place_of_birth'],
                'phone' => $data['phone'],
                'passport_number' => $data['passport_number'],
                'date_of_birth' => $data['date_of_birth'],
                'parent_contact_name' => $data['parent_contact_name'],
                'parent_contact_phone' => $data['parent_contact_phone'],
                'residence_country' => $data['residence_country'],
                'language' => $data['language'],
                'is_studied_in_saudi' => $data['is_studied_in_saudi'],
                'tahseeli_percentage' => $data['tahseeli_percentage'] ?? null,
                'qudorat_percentage' => $data['qudorat_percentage'] ?? null,
            ]);

            return response()->json([
                'message' => 'Profile completed successfully',
                'applicant' => $applicant
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified applicant
     */
    public function show(Applicant $applicant)
    {
        $applicant->load('user', 'qualifications', 'applications');
        return response()->json($applicant);
    }

    /**
     * Update the specified applicant
     */
    public function update(Request $request, Applicant $applicant)
    {
        $data = $request->validate([
            'ar_name' => ['sometimes', 'string', 'max:255'],
            'en_name' => ['sometimes', 'string', 'max:255'],
            'nationality' => ['sometimes', 'string', 'max:100'],
            'gender' => ['sometimes', 'string', 'in:male,female'],
            'place_of_birth' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'passport_number' => ['sometimes', 'string', 'max:50', 'unique:applicants,passport_number,' . $applicant->applicant_id . ',applicant_id'],
            'date_of_birth' => ['sometimes', 'date'],
            'parent_contact_name' => ['sometimes', 'string', 'max:255'],
            'parent_contact_phone' => ['sometimes', 'string', 'max:20'],
            'residence_country' => ['sometimes', 'string', 'max:100'],
            'language' => ['sometimes', 'string', 'max:50'],
            'is_studied_in_saudi' => ['sometimes', 'boolean'],
            'tahseeli_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'qudorat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'passport_copy' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'personal_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'secondary_school_certificate' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'secondary_school_transcript' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'volunteering_certificate' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        try {
            $this->handleFileUploads($request, $applicant);

            $applicant->update([
                'ar_name' => $data['ar_name'] ?? $applicant->ar_name,
                'en_name' => $data['en_name'] ?? $applicant->en_name,
                'nationality' => $data['nationality'] ?? $applicant->nationality,
                'gender' => $data['gender'] ?? $applicant->gender,
                'place_of_birth' => $data['place_of_birth'] ?? $applicant->place_of_birth,
                'phone' => $data['phone'] ?? $applicant->phone,
                'passport_number' => $data['passport_number'] ?? $applicant->passport_number,
                'date_of_birth' => $data['date_of_birth'] ?? $applicant->date_of_birth,
                'parent_contact_name' => $data['parent_contact_name'] ?? $applicant->parent_contact_name,
                'parent_contact_phone' => $data['parent_contact_phone'] ?? $applicant->parent_contact_phone,
                'residence_country' => $data['residence_country'] ?? $applicant->residence_country,
                'language' => $data['language'] ?? $applicant->language,
                'is_studied_in_saudi' => $data['is_studied_in_saudi'] ?? $applicant->is_studied_in_saudi,
                'tahseeli_percentage' => $data['tahseeli_percentage'] ?? $applicant->tahseeli_percentage,
                'qudorat_percentage' => $data['qudorat_percentage'] ?? $applicant->qudorat_percentage,
            ]);

            return response()->json([
                'message' => 'Applicant updated successfully',
                'applicant' => $applicant,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update applicant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified applicant
     */
    public function destroy(Applicant $applicant)
    {
        try {
            $fileFields = [
                'passport_copy_img',
                'personal_image',
                'volunteering_certificate_file',
                'tahsili_file',
                'qudorat_file'
            ];

            foreach ($fileFields as $field) {
                if ($applicant->$field) {
                    Storage::disk('s3')->delete($applicant->$field);
                }
            }

            $applicant->delete();

            return response()->json(['message' => 'Applicant deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete applicant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle file uploads for applicant profile
     */
    private function handleFileUploads(Request $request, Applicant $applicant)
    {
        $documentFields = [
            'passport_copy' => 'passport_copy_img',
            'personal_image' => 'personal_image',
            'secondary_school_certificate' => 'tahsili_file',
            'secondary_school_transcript' => 'qudorat_file',
            'volunteering_certificate' => 'volunteering_certificate_file',
        ];

        foreach ($documentFields as $requestField => $dbField) {
            if ($request->hasFile($requestField)) {
                if ($applicant->$dbField) {
                    Storage::disk('s3')->delete($applicant->$dbField);
                }

                $filename = time() . '_' . str_replace(' ', '_', $request->file($requestField)->getClientOriginalName());
                $filePath = $request->file($requestField)->storeAs("applicants/{$applicant->applicant_id}/documents", $filename, 's3');
                $fullUrl = config('filesystems.disks.s3.url') . '/' . $filePath;
                $applicant->update([$dbField => $fullUrl]);
            }
        }
    }
}
