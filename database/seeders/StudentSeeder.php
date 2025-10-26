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
                    'language_of_study' => null,
                    'yearly_tuition_fees' => $application->tuition_fee,
                    'study_period' => null,
                    'total_semesters_number' => null,
                    'current_semester_number' => $application->current_semester_number,
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

    private function createAdditionalStudents()
    {
        // Get a scholarship for additional students
        $scholarship = \App\Models\Scholarship::first();
        if (!$scholarship) {
            echo "No scholarships found. Cannot create additional students.\n";
            return;
        }

        // Create additional users and applicants
        $additionalStudents = [
            ['first_name' => 'أحمد', 'last_name' => 'محمد', 'email' => 'ahmed.mohamed@example.com', 'ar_name' => 'أحمد محمد'],
            ['first_name' => 'فاطمة', 'last_name' => 'علي', 'email' => 'fatima.ali@example.com', 'ar_name' => 'فاطمة علي'],
            ['first_name' => 'عبدالله', 'last_name' => 'السعد', 'email' => 'abdullah.alsad@example.com', 'ar_name' => 'عبدالله السعد'],
            ['first_name' => 'نورا', 'last_name' => 'الخالد', 'email' => 'nora.alkhalid@example.com', 'ar_name' => 'نورا الخالد'],
            ['first_name' => 'خالد', 'last_name' => 'الرشيد', 'email' => 'khalid.alrasheed@example.com', 'ar_name' => 'خالد الرشيد'],
            ['first_name' => 'سارة', 'last_name' => 'المطيري', 'email' => 'sara.almutairi@example.com', 'ar_name' => 'سارة المطيري'],
            ['first_name' => 'محمد', 'last_name' => 'الغامدي', 'email' => 'mohamed.alghamdi@example.com', 'ar_name' => 'محمد الغامدي'],
            ['first_name' => 'ريم', 'last_name' => 'العتيبي', 'email' => 'reem.alotaibi@example.com', 'ar_name' => 'ريم العتيبي'],
        ];

        $createdCount = 0;
        foreach ($additionalStudents as $studentData) {
            // Check if user already exists
            $existingUser = User::where('email', $studentData['email'])->first();
            if ($existingUser) {
                continue;
            }

            // Create user
            $user = User::create([
                'email' => $studentData['email'],
                'password' => Hash::make('password123'),
                'role' => UserRole::STUDENT,
            ]);

            // Create applicant
            $applicant = Applicant::create([
                'ar_name' => $studentData['ar_name'],
                'en_name' => $studentData['first_name'] . ' ' . $studentData['last_name'],
                'user_id' => $user->user_id,
                'nationality' => 'Saudi',
                'gender' => 'Male',
                'phone' => '0501234567',
            ]);

            // Create applicant application
            $application = \App\Models\ApplicantApplication::create([
                'university_name' => 'King Saud University',
                'country_name' => 'Saudi Arabia',
                'tuition_fee' => 50000.00,
                'cgpa' => 3.5,
                'cgpa_out_of' => 4.0,
                'terms_and_condition' => true,
                'applicant_id' => $applicant->applicant_id,
                'scholarship_id' => $scholarship->scholarship_id,
            ]);

            // Create approved application
            $approvedApp = ApprovedApplicantApplication::create([
                'benefits' => [
                    'tuition_coverage' => '100%',
                    'monthly_stipend' => '2000 SAR',
                    'accommodation' => 'Provided',
                    'health_insurance' => 'Included'
                ],
                'has_accepted_scholarship' => true,
                'scholarship_id' => $scholarship->scholarship_id,
                'application_id' => $application->application_id,
                'user_id' => $user->user_id,
            ]);

            // Create student
            Student::create([
                'user_id' => $user->user_id,
                'applicant_id' => $applicant->applicant_id,
                'approved_application_id' => $approvedApp->approved_application_id,
            ]);

            $createdCount++;
        }

        echo "Created " . $createdCount . " additional students.\n";
    }
}
