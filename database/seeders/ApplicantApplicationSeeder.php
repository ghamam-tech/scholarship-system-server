<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Applicant;
use App\Models\Qualification;
use App\Models\ApplicantApplication;
use App\Models\ApplicantApplicationStatus;
use App\Enums\UserRole;
use App\Enums\ApplicationStatus;

class ApplicantApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create applicant users
        $applicants = [
            [
                'email' => 'ahmed.mohamed@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'أحمد محمد علي',
                    'en_name' => 'Ahmed Mohamed Ali',
                    'nationality' => 'Saudi',
                    'gender' => 'male',
                    'place_of_birth' => 'Riyadh',
                    'phone' => '+966501234567',
                    'passport_number' => 'A12345678',
                    'date_of_birth' => '2000-01-15',
                    'parent_contact_name' => 'Mohamed Ahmed',
                    'parent_contact_phone' => '+966501234568',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 85.5,
                    'qudorat_percentage' => 78.2,
                ]
            ],
            [
                'email' => 'sara.abdullah@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'سارة عبدالله أحمد',
                    'en_name' => 'Sara Abdullah Ahmed',
                    'nationality' => 'Saudi',
                    'gender' => 'female',
                    'place_of_birth' => 'Jeddah',
                    'phone' => '+966502345678',
                    'passport_number' => 'B23456789',
                    'date_of_birth' => '2001-03-20',
                    'parent_contact_name' => 'Abdullah Ibrahim',
                    'parent_contact_phone' => '+966502345679',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 92.0,
                    'qudorat_percentage' => 88.5,
                ]
            ],
            [
                'email' => 'mohammed.hassan@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'محمد حسن سعيد',
                    'en_name' => 'Mohammed Hassan Saeed',
                    'nationality' => 'Egyptian',
                    'gender' => 'male',
                    'place_of_birth' => 'Cairo',
                    'phone' => '+201001234567',
                    'passport_number' => 'C34567890',
                    'date_of_birth' => '1999-07-10',
                    'parent_contact_name' => 'Hassan Mohamed',
                    'parent_contact_phone' => '+201001234568',
                    'residence_country' => 'Egypt',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => false,
                    'tahseeli_percentage' => null,
                    'qudorat_percentage' => null,
                ]
            ],
            [
                'email' => 'fatima.omar@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'فاطمة عمر خالد',
                    'en_name' => 'Fatima Omar Khaled',
                    'nationality' => 'Saudi',
                    'gender' => 'female',
                    'place_of_birth' => 'Dammam',
                    'phone' => '+966503456789',
                    'passport_number' => 'D45678901',
                    'date_of_birth' => '2000-11-05',
                    'parent_contact_name' => 'Omar Abdulrahman',
                    'parent_contact_phone' => '+966503456780',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 79.8,
                    'qudorat_percentage' => 82.3,
                ]
            ],
            [
                'email' => 'khalid.ibrahim@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'خالد إبراهيم محمد',
                    'en_name' => 'Khalid Ibrahim Mohammed',
                    'nationality' => 'Saudi',
                    'gender' => 'male',
                    'place_of_birth' => 'Medina',
                    'phone' => '+966504567890',
                    'passport_number' => 'E56789012',
                    'date_of_birth' => '1998-12-25',
                    'parent_contact_name' => 'Ibrahim Khalid',
                    'parent_contact_phone' => '+966504567891',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 87.2,
                    'qudorat_percentage' => 90.1,
                ]
            ]
        ];

        foreach ($applicants as $applicantData) {
            // Create user
            $user = User::create([
                'email' => $applicantData['email'],
                'password' => Hash::make($applicantData['password']),
                'role' => UserRole::APPLICANT->value,
            ]);

            // Create applicant
            $applicant = Applicant::create(array_merge(
                $applicantData['applicant_data'],
                ['user_id' => $user->user_id]
            ));

            // Create qualifications for each applicant
            $this->createQualifications($applicant);

            // Create applications for some applicants
            $this->createApplications($applicant);
        }

        $this->command->info('✅ 5 applicants created with qualifications and applications!');
        $this->command->info('📧 All applicants have password: password123');
    }

    private function createQualifications(Applicant $applicant): void
    {
        $qualifications = [];

        // All applicants have high school qualification
        $qualifications[] = [
            'applicant_id' => $applicant->applicant_id,
            'qualification_type' => 'high_school',
            'institute_name' => $applicant->place_of_birth . ' High School',
            'year_of_graduation' => 2019,
            'cgpa' => rand(34, 39) / 10, // 3.4 - 3.9 on 4.0 scale
            'cgpa_out_of' => 4.0,
            'language_of_study' => 'Arabic',
            'specialization' => 'Science',
            'research_title' => null,
            'document_file' => 'https://example.com/documents/high_school_' . $applicant->applicant_id . '.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add bachelor's degree for most applicants
        if (rand(0, 1)) {
            $qualifications[] = [
                'applicant_id' => $applicant->applicant_id,
                'qualification_type' => 'bachelor',
                'institute_name' => 'King Saud University',
                'year_of_graduation' => 2023,
                'cgpa' => rand(34, 39) / 10, // 3.4 - 3.9
                'cgpa_out_of' => 4.0,
                'language_of_study' => 'Arabic',
                'specialization' => 'Computer Science',
                'research_title' => 'Software Engineering Principles',
                'document_file' => 'https://example.com/documents/bachelor_' . $applicant->applicant_id . '.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Add master's degree for some applicants
        if (rand(0, 3) === 0) {
            $qualifications[] = [
                'applicant_id' => $applicant->applicant_id,
                'qualification_type' => 'master',
                'institute_name' => 'King Abdulaziz University',
                'year_of_graduation' => 2024,
                'cgpa' => rand(36, 40) / 10, // 3.6 - 4.0
                'cgpa_out_of' => 4.0,
                'language_of_study' => 'English',
                'specialization' => 'Artificial Intelligence',
                'research_title' => 'Machine Learning Applications in Healthcare',
                'document_file' => 'https://example.com/documents/master_' . $applicant->applicant_id . '.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Add diploma for some applicants
        if (rand(0, 2) === 0) {
            $qualifications[] = [
                'applicant_id' => $applicant->applicant_id,
                'qualification_type' => 'diploma',
                'institute_name' => 'Technical College ' . $applicant->place_of_birth,
                'year_of_graduation' => 2020,
                'cgpa' => rand(35, 38) / 10, // 3.5 - 3.8
                'cgpa_out_of' => 4.0,
                'language_of_study' => 'Arabic',
                'specialization' => 'Information Technology',
                'research_title' => null,
                'document_file' => 'https://example.com/documents/diploma_' . $applicant->applicant_id . '.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Qualification::insert($qualifications);
    }

    private function createApplications(Applicant $applicant): void
    {
        $scholarships = [1, 2, 3, 4, 5, 6, 7, 8]; // Active scholarship IDs from your table

        // Each applicant has 1-2 applications
        $applicationCount = rand(1, 2);

        for ($i = 0; $i < $applicationCount; $i++) {
            $scholarshipId = $scholarships[array_rand($scholarships)];

            // Different universities and countries for variety
            $universities = [
                ['Stanford University', 'USA'],
                ['MIT', 'USA'],
                ['Harvard University', 'USA'],
                ['University of Oxford', 'UK'],
                ['University of Cambridge', 'UK'],
                ['King Saud University', 'Saudi Arabia'],
                ['King Abdulaziz University', 'Saudi Arabia'],
            ];

            $universityData = $universities[array_rand($universities)];

            $application = ApplicantApplication::create([
                'applicant_id' => $applicant->applicant_id,
                'scholarship_id' => $scholarshipId, // Changed from scholarship_id_1 to scholarship_id
                'specialization_1' => 'Computer Science',
                'specialization_2' => 'Data Science',
                'specialization_3' => 'Artificial Intelligence',
                'university_name' => $universityData[0],
                'country_name' => $universityData[1],
                'tuition_fee' => $universityData[1] === 'Saudi Arabia' ? rand(10000, 20000) : rand(30000, 60000),
                'has_active_program' => true,
                'current_semester_number' => rand(1, 4),
                'cgpa' => rand(34, 39) / 10,
                'cgpa_out_of' => 4.0,
                'terms_and_condition' => true,
                'offer_letter_file' => 'https://example.com/documents/offer_letter_' . $applicant->applicant_id . '_' . $i . '.pdf',
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);

            // Create application status history
            $this->createApplicationStatuses($application);
        }
    }

    private function createApplicationStatuses(ApplicantApplication $application): void
    {
        $statuses = [];
        $currentDate = $application->created_at;

        // Initial enrolled status
        $statuses[] = [
            'application_id' => $application->application_id,
            'status_name' => ApplicationStatus::ENROLLED->value,
            'date' => $currentDate,
            'comment' => 'Application submitted successfully',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ];

        // Random progression through statuses
        $possibleStatuses = [
            ApplicationStatus::FIRST_APPROVAL->value,
            ApplicationStatus::SECOND_APPROVAL->value,
            ApplicationStatus::FINAL_APPROVAL->value,
            ApplicationStatus::REJECTED->value,
        ];

        $currentStatus = ApplicationStatus::ENROLLED->value;

        foreach ($possibleStatuses as $status) {
            // 70% chance to progress to next status, 30% to stop or get rejected
            if (rand(0, 100) > 70) {
                break;
            }

            $currentDate = $currentDate->addDays(rand(2, 7));

            $statuses[] = [
                'application_id' => $application->application_id,
                'status_name' => $status,
                'date' => $currentDate,
                'comment' => $this->getStatusComment($status),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ];

            $currentStatus = $status;

            // If rejected, stop the progression
            if ($status === ApplicationStatus::REJECTED->value) {
                break;
            }
        }

        ApplicantApplicationStatus::insert($statuses);
    }

    private function getStatusComment(string $status): string
    {
        $comments = [
            ApplicationStatus::ENROLLED->value => 'Application received and under initial review',
            ApplicationStatus::FIRST_APPROVAL->value => 'Application passed initial screening',
            ApplicationStatus::SECOND_APPROVAL->value => 'Documents verified, proceeding to final review',
            ApplicationStatus::FINAL_APPROVAL->value => 'Congratulations! Application fully approved',
            ApplicationStatus::REJECTED->value => 'Application did not meet all requirements',
        ];

        return $comments[$status] ?? 'Status updated';
    }
}
