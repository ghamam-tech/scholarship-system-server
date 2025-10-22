<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Applicant;
use App\Models\Scholarship;
use App\Models\ApplicantApplication;
use App\Models\Qualification;
use App\Models\ApplicantApplicationStatus;
use App\Enums\UserRole;
use App\Enums\ApplicationStatus;
use Illuminate\Support\Facades\Storage;

class ComprehensiveTestSeeder extends Seeder
{
    public function run()
    {
        echo "=== COMPREHENSIVE TEST SEEDER ===\n";

        // Create test users with different roles
        $this->createTestUsers();

        // Create test applicants with profiles
        $this->createTestApplicants();

        // Create test applications with file uploads
        $this->createTestApplications();

        echo "✅ Comprehensive test data created successfully!\n";
    }

    private function createTestUsers()
    {
        echo "👥 Creating test users...\n";

        $users = [
            [
                'name' => 'Ahmed Al-Rashid',
                'email' => 'ahmed.rashid@test.com',
                'password' => bcrypt('password123'),
                'role' => UserRole::APPLICANT->value
            ],
            [
                'name' => 'Fatima Al-Zahra',
                'email' => 'fatima.zahra@test.com',
                'password' => bcrypt('password123'),
                'role' => UserRole::APPLICANT->value
            ],
            [
                'name' => 'Mohammed Al-Sayed',
                'email' => 'mohammed.sayed@test.com',
                'password' => bcrypt('password123'),
                'role' => UserRole::APPLICANT->value
            ],
            [
                'name' => 'Aisha Al-Mansouri',
                'email' => 'aisha.mansouri@test.com',
                'password' => bcrypt('password123'),
                'role' => UserRole::APPLICANT->value
            ],
            [
                'name' => 'Omar Al-Hassan',
                'email' => 'omar.hassan@test.com',
                'password' => bcrypt('password123'),
                'role' => UserRole::APPLICANT->value
            ]
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);
            echo "✅ Created user: {$user->name} ({$user->email})\n";
        }
    }

    private function createTestApplicants()
    {
        echo "👤 Creating test applicants...\n";

        $users = User::where('role', UserRole::APPLICANT->value)->get();

        $applicantData = [
            [
                'ar_name' => 'أحمد الراشد',
                'en_name' => 'Ahmed Al-Rashid',
                'nationality' => 'Saudi',
                'gender' => 'male',
                'place_of_birth' => 'Riyadh',
                'phone' => '+966501234567',
                'passport_number' => 'A12345678',
                'date_of_birth' => '1998-05-15',
                'parent_contact_name' => 'Abdullah Al-Rashid',
                'parent_contact_phone' => '+966501234568',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
                'tahseeli_percentage' => 92.5,
                'qudorat_percentage' => 88.3
            ],
            [
                'ar_name' => 'فاطمة الزهراء',
                'en_name' => 'Fatima Al-Zahra',
                'nationality' => 'Saudi',
                'gender' => 'female',
                'place_of_birth' => 'Jeddah',
                'phone' => '+966501234569',
                'passport_number' => 'B23456789',
                'date_of_birth' => '1999-08-22',
                'parent_contact_name' => 'Hassan Al-Zahra',
                'parent_contact_phone' => '+966501234570',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
                'tahseeli_percentage' => 95.2,
                'qudorat_percentage' => 91.7
            ],
            [
                'ar_name' => 'محمد السيد',
                'en_name' => 'Mohammed Al-Sayed',
                'nationality' => 'Saudi',
                'gender' => 'male',
                'place_of_birth' => 'Dammam',
                'phone' => '+966501234571',
                'passport_number' => 'C34567890',
                'date_of_birth' => '1997-12-10',
                'parent_contact_name' => 'Ibrahim Al-Sayed',
                'parent_contact_phone' => '+966501234572',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
                'tahseeli_percentage' => 89.8,
                'qudorat_percentage' => 85.4
            ],
            [
                'ar_name' => 'عائشة المنصوري',
                'en_name' => 'Aisha Al-Mansouri',
                'nationality' => 'Saudi',
                'gender' => 'female',
                'place_of_birth' => 'Mecca',
                'phone' => '+966501234573',
                'passport_number' => 'D45678901',
                'date_of_birth' => '2000-03-18',
                'parent_contact_name' => 'Khalid Al-Mansouri',
                'parent_contact_phone' => '+966501234574',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
                'tahseeli_percentage' => 94.1,
                'qudorat_percentage' => 90.2
            ],
            [
                'ar_name' => 'عمر الحسن',
                'en_name' => 'Omar Al-Hassan',
                'nationality' => 'Saudi',
                'gender' => 'male',
                'place_of_birth' => 'Medina',
                'phone' => '+966501234575',
                'passport_number' => 'E56789012',
                'date_of_birth' => '1996-11-25',
                'parent_contact_name' => 'Yusuf Al-Hassan',
                'parent_contact_phone' => '+966501234576',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
                'tahseeli_percentage' => 87.6,
                'qudorat_percentage' => 83.9
            ]
        ];

        foreach ($users as $index => $user) {
            if (isset($applicantData[$index])) {
                $applicant = Applicant::create([
                    'user_id' => $user->id,
                    'ar_name' => $applicantData[$index]['ar_name'],
                    'en_name' => $applicantData[$index]['en_name'],
                    'nationality' => $applicantData[$index]['nationality'],
                    'gender' => $applicantData[$index]['gender'],
                    'place_of_birth' => $applicantData[$index]['place_of_birth'],
                    'phone' => $applicantData[$index]['phone'],
                    'passport_number' => $applicantData[$index]['passport_number'],
                    'date_of_birth' => $applicantData[$index]['date_of_birth'],
                    'parent_contact_name' => $applicantData[$index]['parent_contact_name'],
                    'parent_contact_phone' => $applicantData[$index]['parent_contact_phone'],
                    'residence_country' => $applicantData[$index]['residence_country'],
                    'language' => $applicantData[$index]['language'],
                    'is_studied_in_saudi' => $applicantData[$index]['is_studied_in_saudi'],
                    'tahseeli_percentage' => $applicantData[$index]['tahseeli_percentage'],
                    'qudorat_percentage' => $applicantData[$index]['qudorat_percentage']
                ]);
                echo "✅ Created applicant: {$applicant->en_name} (ID: {$applicant->applicant_id})\n";
            }
        }
    }

    private function createTestApplications()
    {
        echo "📋 Creating test applications with file uploads...\n";

        $applicants = Applicant::all();
        $scholarships = Scholarship::where('is_active', true)->get();

        if ($scholarships->isEmpty()) {
            echo "❌ No active scholarships found. Creating test scholarship...\n";
            $scholarship = Scholarship::create([
                'title' => 'Test Scholarship Program',
                'description' => 'A comprehensive scholarship program for testing',
                'sponsor_id' => 1,
                'is_active' => true,
                'is_hided' => false,
                'opening_date' => now()->subDays(30),
                'closing_date' => now()->addDays(60),
                'requirements' => 'High school diploma, good grades',
                'benefits' => 'Full tuition coverage, monthly stipend',
                'application_process' => 'Online application with document upload'
            ]);
            $scholarships = collect([$scholarship]);
        }

        foreach ($applicants as $index => $applicant) {
            $scholarship = $scholarships->first();

            // Upload test files for this applicant
            $filePaths = $this->uploadTestFiles($applicant, $index);

            // Create application
            $application = ApplicantApplication::create([
                'applicant_id' => $applicant->applicant_id,
                'scholarship_id_1' => $scholarship->id,
                'specialization_1' => 'Computer Science',
                'specialization_2' => 'Data Science',
                'specialization_3' => 'Artificial Intelligence',
                'university_name' => 'King Saud University',
                'country_name' => 'Saudi Arabia',
                'tuition_fee' => 50000 + ($index * 10000),
                'has_active_program' => true,
                'current_semester_number' => 1 + $index,
                'cgpa' => 3.5 + ($index * 0.1),
                'cgpa_out_of' => 4.0,
                'terms_and_condition' => true,
                'offer_letter_file' => $filePaths['offer_letter'] ?? null,
            ]);

            // Update applicant with file paths
            $applicant->update([
                'passport_copy_img' => $filePaths['passport_copy'] ?? null,
                'personal_image' => $filePaths['personal_image'] ?? null,
                'tahsili_file' => $filePaths['tahsili'] ?? null,
                'qudorat_file' => $filePaths['qudorat'] ?? null,
                'volunteering_certificate_file' => $filePaths['volunteering'] ?? null,
            ]);

            // Create qualifications
            $this->createQualifications($applicant, $filePaths);

            // Create application status
            ApplicantApplicationStatus::create([
                'user_id' => $applicant->user_id,
                'status_name' => ApplicationStatus::ENROLLED->value,
                'status_date' => now(),
                'notes' => 'Application submitted successfully'
            ]);

            echo "✅ Created application for {$applicant->en_name} (ID: {$application->application_id})\n";
        }
    }

    private function uploadTestFiles($applicant, $index)
    {
        $timestamp = time() + $index;
        $filePaths = [];

        // Create test file content
        $testFiles = [
            'passport_copy' => 'applicant-documents/passport/',
            'personal_image' => 'applicant-documents/personal-images/',
            'tahsili' => 'applicant-documents/tahsili/',
            'qudorat' => 'applicant-documents/qudorat/',
            'volunteering' => 'applicant-documents/volunteering/',
            'offer_letter' => 'application-documents/offer-letters/',
        ];

        foreach ($testFiles as $fileType => $folder) {
            try {
                $filename = $timestamp . '_' . $applicant->en_name . '_' . $fileType . '.pdf';
                $fullPath = $folder . $filename;

                // Create test content
                $content = "Test {$fileType} file for {$applicant->en_name} - Generated at " . now();

                // Upload to S3
                Storage::disk('s3')->put($fullPath, $content);
                $filePaths[$fileType] = $fullPath;

                echo "  📁 Uploaded {$fileType}: {$fullPath}\n";
            } catch (Exception $e) {
                echo "  ❌ Failed to upload {$fileType}: " . $e->getMessage() . "\n";
            }
        }

        return $filePaths;
    }

    private function createQualifications($applicant, $filePaths)
    {
        $qualifications = [
            [
                'qualification_type' => 'high_school',
                'institute_name' => 'Al-Nahda High School',
                'year_of_graduation' => 2019,
                'cgpa' => 95.5,
                'cgpa_out_of' => 99.99,
                'language_of_study' => 'Arabic',
                'specialization' => 'Science',
                'document_file' => $filePaths['tahsili'] ?? null,
            ],
            [
                'qualification_type' => 'bachelor',
                'institute_name' => 'King Saud University',
                'year_of_graduation' => 2023,
                'cgpa' => 3.8,
                'cgpa_out_of' => 4.0,
                'language_of_study' => 'Arabic',
                'specialization' => 'Computer Science',
                'document_file' => $filePaths['qudorat'] ?? null,
            ]
        ];

        foreach ($qualifications as $qualData) {
            $qualification = Qualification::create([
                'applicant_id' => $applicant->applicant_id,
                ...$qualData
            ]);
            echo "  📚 Created qualification: {$qualification->qualification_type}\n";
        }
    }
}
