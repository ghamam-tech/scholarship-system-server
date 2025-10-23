<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Student;
use App\Models\ApprovedApplicantApplication;
use App\Models\Country;
use App\Models\University;
use App\Enums\UserRole;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('approved_applicant_applications')) {
            $this->command?->warn('No approved_applicant_applications table found. No students created.');
            return;
        }

        $approvedApplicants = ApprovedApplicantApplication::with([
            'application.applicant.user',
            'scholarship.countries',
            'scholarship.universities',
        ])->get();

        if ($approvedApplicants->isEmpty()) {
            $this->command?->warn('No approved applicant applications available to create students.');
            return;
        }

        $created = 0;

        foreach ($approvedApplicants as $approved) {
            $application = $approved->application;
            $applicant = $application?->applicant;
            $user = $applicant?->user;
            $scholarship = $approved->scholarship;

            if (!$application || !$applicant || !$user || !$scholarship) {
                continue;
            }

            $country = $scholarship->countries->first();
            if (!$country && $application->country_name) {
                $country = Country::where('country_name', $application->country_name)->first();
            }

            $university = $scholarship->universities->first();
            if (!$university && $application->university_name) {
                $university = University::where('university_name', $application->university_name)->first();
            }

            $student = Student::updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'applicant_id' => $applicant->applicant_id,
                    'approved_application_id' => $approved->approved_application_id,
                    'specialization' => $application->specialization_1,
                    'offer_letter' => $application->offer_letter_file,
                    'country_id' => $country?->country_id,
                    'university_id' => $university?->university_id,
                ]
            );

            if ($student->wasRecentlyCreated) {
                $created++;
            }

            if (!$applicant->is_archive) {
                $applicant->update(['is_archive' => 1]);
            }

            if ($user->role !== UserRole::STUDENT) {
                $user->update(['role' => UserRole::STUDENT->value]);
            }
        }

        $this->command?->info("Created {$created} student records from approved applicants.");
    }
}
