<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ApplicantController extends Controller
{
    /**
     * Display a listing of applicants
     */
    public function index()
    {
        $applicants = Applicant::with('user')->get();
        return response()->json($applicants);
    }

    /**
     * Store a newly created applicant
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'ar_name' => ['required', 'string', 'max:255'],
            'en_name' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'string', 'in:male,female'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'passport_number' => ['required', 'string', 'max:50', 'unique:applicants,passport_number'],
            'date_of_birth' => ['required', 'date'],
            'parent_contact_name' => ['required', 'string', 'max:255'],
            'parent_contact_phone' => ['required', 'string', 'max:20'],
            'residence_country' => ['required', 'string', 'max:100'],
            'language' => ['required', 'string', 'max:50'],
            'is_studied_in_saudi' => ['boolean'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', Password::min(8)],
            'passport_copy_img' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'volunteering_certificate_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'tahsili_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'qudorat_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        // Create user account
        $user = User::create([
            'email' => $data['email'],
            'password' => $data['password'] ?? Str::random(12),
            'role' => UserRole::APPLICANT->value,
        ]);

        // Handle file uploads to S3
        $passportCopyUrl = null;
        $volunteeringCertUrl = null;
        $tahsiliFileUrl = null;
        $qudoratFileUrl = null;

        if ($request->hasFile('passport_copy_img')) {
            $passportCopyUrl = $request->file('passport_copy_img')->store('applicants/passport', 's3');
        }

        if ($request->hasFile('volunteering_certificate_file')) {
            $volunteeringCertUrl = $request->file('volunteering_certificate_file')->store('applicants/volunteering', 's3');
        }

        if ($request->hasFile('tahsili_file')) {
            $tahsiliFileUrl = $request->file('tahsili_file')->store('applicants/tahsili', 's3');
        }

        if ($request->hasFile('qudorat_file')) {
            $qudoratFileUrl = $request->file('qudorat_file')->store('applicants/qudorat', 's3');
        }

        // Create applicant profile
        $applicant = Applicant::create([
            'user_id' => $user->user_id,
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
            'is_studied_in_saudi' => $data['is_studied_in_saudi'] ?? false,
            'passport_copy_img' => $passportCopyUrl,
            'volunteering_certificate_file' => $volunteeringCertUrl,
            'tahsili_file' => $tahsiliFileUrl,
            'qudorat_file' => $qudoratFileUrl,
        ]);

        return response()->json([
            'message' => 'Applicant created successfully',
            'user' => $user,
            'applicant' => $applicant,
        ], 201);
    }

    /**
     * Display the specified applicant
     */
    public function show(Applicant $applicant)
    {
        $applicant->load('user');
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
            'passport_copy_img' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'volunteering_certificate_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'tahsili_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'qudorat_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
        ]);

        // Handle file uploads to S3
        if ($request->hasFile('passport_copy_img')) {
            // Delete old file if exists
            if ($applicant->passport_copy_img) {
                Storage::disk('s3')->delete($applicant->passport_copy_img);
            }
            $data['passport_copy_img'] = $request->file('passport_copy_img')->store('applicants/passport', 's3');
        }

        if ($request->hasFile('volunteering_certificate_file')) {
            if ($applicant->volunteering_certificate_file) {
                Storage::disk('s3')->delete($applicant->volunteering_certificate_file);
            }
            $data['volunteering_certificate_file'] = $request->file('volunteering_certificate_file')->store('applicants/volunteering', 's3');
        }

        if ($request->hasFile('tahsili_file')) {
            if ($applicant->tahsili_file) {
                Storage::disk('s3')->delete($applicant->tahsili_file);
            }
            $data['tahsili_file'] = $request->file('tahsili_file')->store('applicants/tahsili', 's3');
        }

        if ($request->hasFile('qudorat_file')) {
            if ($applicant->qudorat_file) {
                Storage::disk('s3')->delete($applicant->qudorat_file);
            }
            $data['qudorat_file'] = $request->file('qudorat_file')->store('applicants/qudorat', 's3');
        }

        $applicant->update($data);

        return response()->json([
            'message' => 'Applicant updated successfully',
            'applicant' => $applicant,
        ]);
    }

    /**
     * Remove the specified applicant
     */
    public function destroy(Applicant $applicant)
    {
        // Delete files from S3
        if ($applicant->passport_copy_img) {
            Storage::disk('s3')->delete($applicant->passport_copy_img);
        }
        if ($applicant->volunteering_certificate_file) {
            Storage::disk('s3')->delete($applicant->volunteering_certificate_file);
        }
        if ($applicant->tahsili_file) {
            Storage::disk('s3')->delete($applicant->tahsili_file);
        }
        if ($applicant->qudorat_file) {
            Storage::disk('s3')->delete($applicant->qudorat_file);
        }

        $applicant->delete();
        return response()->json(['message' => 'Applicant deleted successfully']);
    }
}
