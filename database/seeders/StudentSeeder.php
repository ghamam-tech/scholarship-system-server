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
        if (\Schema::hasTable('approved_applicant_applications')) {
            // Get existing applicants with approved applications
            $approvedApplicants = ApprovedApplicantApplication::with(['application.applicant.user'])->get();

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
                }
            }

            echo "Created " . $approvedApplicants->count() . " students from approved applicants.\n";
        } else {
            echo "No approved_applicant_applications table found. No students created.\n";
        }
    }

}
