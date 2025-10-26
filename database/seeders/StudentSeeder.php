<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Student;
use App\Models\Applicant;
use App\Models\ApprovedApplicantApplication;
use App\Enums\UserRole;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Check if approved_applicant_applications table exists
        if (Schema::hasTable('approved_applicant_applications')) {
            // Get existing applicants with approved applications
            $approvedApplicants = ApprovedApplicantApplication::with(['application.applicant.user'])->get();

            $createdCount = 0;
            foreach ($approvedApplicants as $approvedApp) {
                $applicant = $approvedApp->application->applicant;
                $user = $applicant->user;

                // Check if student already exists for this user
                $existingStudent = Student::where('user_id', $user->user_id)->first();

                if (!$existingStudent) {
                    // Create student record with only foreign keys
                    Student::create([
                        'user_id' => $user->user_id,
                        'applicant_id' => $applicant->applicant_id,
                        'approved_application_id' => $approvedApp->approved_application_id,
                    ]);

                    // Update user role to student
                    $user->update(['role' => UserRole::STUDENT]);
                    $createdCount++;
                }
            }

            // Create additional students if we have less than 10
            if ($createdCount < 10) {
                $this->createAdditionalStudents();
            }

            echo "Created " . $createdCount . " students from approved applicants.\n";
        } else {
            echo "No approved_applicant_applications table found. No students created.\n";
        }
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
