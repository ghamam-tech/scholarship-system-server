<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Applicant;
use App\Models\ApplicantApplication;
use App\Models\ApplicantApplicationStatus;
use App\Models\Qualification;
use App\Models\Scholarship;
use App\Enums\UserRole;
use App\Enums\ApplicationStatus;

class ApplicantApplicationSeeder extends Seeder
{
    public function run()
    {
        // Get available scholarships
        $scholarships = Scholarship::where('is_active', true)
            ->where('is_hided', false)
            ->where('closing_date', '>', now())
            ->get();

        if ($scholarships->count() < 3) {
            $this->command->warn('⚠️  Need at least 3 active scholarships for seeding applications');
            return;
        }

        $applications = [
            [
                'personal_info' => [
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
                    'qudorat_percentage' => 78.2
                ],
                'academic_info' => [
                    'qualifications' => [
                        [
                            'qualification_type' => 'high_school',
                            'institute_name' => 'Al Nahda School',
                            'year_of_graduation' => 2019,
                            'cgpa' => 98.5,
                            'cgpa_out_of' => 99.99,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Science'
                        ],
                        [
                            'qualification_type' => 'bachelor',
                            'institute_name' => 'King Saud University',
                            'year_of_graduation' => 2023,
                            'cgpa' => 3.8,
                            'cgpa_out_of' => 4.0,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Computer Science'
                        ]
                    ]
                ],
                'program_details' => [
                    'scholarship_ids' => [$scholarships[0]->scholarship_id, $scholarships[1]->scholarship_id ?? $scholarships[0]->scholarship_id],
                    'specialization_1' => 'Computer Science',
                    'specialization_2' => 'Data Science',
                    'specialization_3' => 'Artificial Intelligence',
                    'university_name' => 'Stanford University',
                    'country_name' => 'USA',
                    'tuition_fee' => 50000,
                    'has_active_program' => true,
                    'current_semester_number' => 2,
                    'cgpa' => 3.75,
                    'cgpa_out_of' => 4.0,
                    'terms_and_condition' => true
                ],
                'status' => ApplicationStatus::FIRST_APPROVAL
            ],
            [
                'personal_info' => [
                    'ar_name' => 'فاطمة أحمد السعد',
                    'en_name' => 'Fatima Ahmed Al-Saad',
                    'nationality' => 'Saudi',
                    'gender' => 'female',
                    'place_of_birth' => 'Jeddah',
                    'phone' => '+966501234569',
                    'passport_number' => 'A87654321',
                    'date_of_birth' => '1999-05-20',
                    'parent_contact_name' => 'Ahmed Al-Saad',
                    'parent_contact_phone' => '+966501234570',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 92.3,
                    'qudorat_percentage' => 88.7
                ],
                'academic_info' => [
                    'qualifications' => [
                        [
                            'qualification_type' => 'high_school',
                            'institute_name' => 'Al-Faisal School',
                            'year_of_graduation' => 2018,
                            'cgpa' => 99.2,
                            'cgpa_out_of' => 99.99,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Science'
                        ],
                        [
                            'qualification_type' => 'bachelor',
                            'institute_name' => 'King Abdulaziz University',
                            'year_of_graduation' => 2022,
                            'cgpa' => 3.9,
                            'cgpa_out_of' => 4.0,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Engineering'
                        ],
                        [
                            'qualification_type' => 'master',
                            'institute_name' => 'King Abdulaziz University',
                            'year_of_graduation' => 2024,
                            'cgpa' => 3.95,
                            'cgpa_out_of' => 4.0,
                            'language_of_study' => 'English',
                            'specialization' => 'Mechanical Engineering',
                            'research_title' => 'Advanced Materials in Aerospace Applications'
                        ]
                    ]
                ],
                'program_details' => [
                    'scholarship_ids' => [$scholarships[1]->scholarship_id ?? $scholarships[0]->scholarship_id, $scholarships[2]->scholarship_id ?? $scholarships[0]->scholarship_id],
                    'specialization_1' => 'Mechanical Engineering',
                    'specialization_2' => 'Aerospace Engineering',
                    'specialization_3' => 'Materials Science',
                    'university_name' => 'MIT',
                    'country_name' => 'USA',
                    'tuition_fee' => 75000,
                    'has_active_program' => false,
                    'current_semester_number' => null,
                    'cgpa' => null,
                    'cgpa_out_of' => null,
                    'terms_and_condition' => true
                ],
                'status' => ApplicationStatus::SECOND_APPROVAL
            ],
            [
                'personal_info' => [
                    'ar_name' => 'خالد عبدالله المطيري',
                    'en_name' => 'Khalid Abdullah Al-Mutairi',
                    'nationality' => 'Saudi',
                    'gender' => 'male',
                    'place_of_birth' => 'Dammam',
                    'phone' => '+966501234571',
                    'passport_number' => 'A11223344',
                    'date_of_birth' => '2001-08-10',
                    'parent_contact_name' => 'Abdullah Al-Mutairi',
                    'parent_contact_phone' => '+966501234572',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 78.9,
                    'qudorat_percentage' => 82.1
                ],
                'academic_info' => [
                    'qualifications' => [
                        [
                            'qualification_type' => 'high_school',
                            'institute_name' => 'Al-Khobar School',
                            'year_of_graduation' => 2020,
                            'cgpa' => 95.8,
                            'cgpa_out_of' => 99.99,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Science'
                        ],
                        [
                            'qualification_type' => 'bachelor',
                            'institute_name' => 'University of Dammam',
                            'year_of_graduation' => 2024,
                            'cgpa' => 3.6,
                            'cgpa_out_of' => 4.0,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Business Administration'
                        ]
                    ]
                ],
                'program_details' => [
                    'scholarship_ids' => [$scholarships[2]->scholarship_id ?? $scholarships[0]->scholarship_id],
                    'specialization_1' => 'Business Administration',
                    'specialization_2' => 'Finance',
                    'specialization_3' => 'Marketing',
                    'university_name' => 'Harvard Business School',
                    'country_name' => 'USA',
                    'tuition_fee' => 100000,
                    'has_active_program' => false,
                    'current_semester_number' => null,
                    'cgpa' => null,
                    'cgpa_out_of' => null,
                    'terms_and_condition' => true
                ],
                'status' => ApplicationStatus::ENROLLED
            ],
            [
                'personal_info' => [
                    'ar_name' => 'نورا سعد القحطاني',
                    'en_name' => 'Nora Saad Al-Qahtani',
                    'nationality' => 'Saudi',
                    'gender' => 'female',
                    'place_of_birth' => 'Abha',
                    'phone' => '+966501234573',
                    'passport_number' => 'A55667788',
                    'date_of_birth' => '1998-12-03',
                    'parent_contact_name' => 'Saad Al-Qahtani',
                    'parent_contact_phone' => '+966501234574',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 89.4,
                    'qudorat_percentage' => 91.2
                ],
                'academic_info' => [
                    'qualifications' => [
                        [
                            'qualification_type' => 'high_school',
                            'institute_name' => 'Al-Ahsa School',
                            'year_of_graduation' => 2017,
                            'cgpa' => 97.1,
                            'cgpa_out_of' => 99.99,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Science'
                        ],
                        [
                            'qualification_type' => 'bachelor',
                            'institute_name' => 'King Faisal University',
                            'year_of_graduation' => 2021,
                            'cgpa' => 3.7,
                            'cgpa_out_of' => 4.0,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Medicine'
                        ]
                    ]
                ],
                'program_details' => [
                    'scholarship_ids' => [$scholarships[0]->scholarship_id, $scholarships[1]->scholarship_id ?? $scholarships[0]->scholarship_id],
                    'specialization_1' => 'Medicine',
                    'specialization_2' => 'Surgery',
                    'specialization_3' => 'Cardiology',
                    'university_name' => 'Johns Hopkins University',
                    'country_name' => 'USA',
                    'tuition_fee' => 120000,
                    'has_active_program' => true,
                    'current_semester_number' => 4,
                    'cgpa' => 3.8,
                    'cgpa_out_of' => 4.0,
                    'terms_and_condition' => true
                ],
                'status' => ApplicationStatus::FINAL_APPROVAL
            ],
            [
                'personal_info' => [
                    'ar_name' => 'عبدالرحمن محمد الشمري',
                    'en_name' => 'Abdulrahman Mohamed Al-Shammari',
                    'nationality' => 'Saudi',
                    'gender' => 'male',
                    'place_of_birth' => 'Hail',
                    'phone' => '+966501234575',
                    'passport_number' => 'A99887766',
                    'date_of_birth' => '2002-03-25',
                    'parent_contact_name' => 'Mohamed Al-Shammari',
                    'parent_contact_phone' => '+966501234576',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 76.8,
                    'qudorat_percentage' => 79.3
                ],
                'academic_info' => [
                    'qualifications' => [
                        [
                            'qualification_type' => 'high_school',
                            'institute_name' => 'Hail School',
                            'year_of_graduation' => 2021,
                            'cgpa' => 93.2,
                            'cgpa_out_of' => 99.99,
                            'language_of_study' => 'Arabic',
                            'specialization' => 'Science'
                        ]
                    ]
                ],
                'program_details' => [
                    'scholarship_ids' => [$scholarships[1]->scholarship_id ?? $scholarships[0]->scholarship_id],
                    'specialization_1' => 'Computer Science',
                    'specialization_2' => 'Software Engineering',
                    'specialization_3' => 'Cybersecurity',
                    'university_name' => 'University of California, Berkeley',
                    'country_name' => 'USA',
                    'tuition_fee' => 45000,
                    'has_active_program' => false,
                    'current_semester_number' => null,
                    'cgpa' => null,
                    'cgpa_out_of' => null,
                    'terms_and_condition' => true
                ],
                'status' => ApplicationStatus::REJECTED
            ]
        ];

        foreach ($applications as $index => $appData) {
            try {
                DB::beginTransaction();

                // Create user
                $user = User::create([
                    'email' => "applicant" . ($index + 1) . "@example.com",
                    'password' => bcrypt('password123'),
                    'role' => UserRole::APPLICANT->value
                ]);

                // Create applicant profile
                $applicant = Applicant::create([
                    'user_id' => $user->user_id,
                    'ar_name' => $appData['personal_info']['ar_name'],
                    'en_name' => $appData['personal_info']['en_name'],
                    'nationality' => $appData['personal_info']['nationality'],
                    'gender' => $appData['personal_info']['gender'],
                    'place_of_birth' => $appData['personal_info']['place_of_birth'],
                    'phone' => $appData['personal_info']['phone'],
                    'passport_number' => $appData['personal_info']['passport_number'],
                    'date_of_birth' => $appData['personal_info']['date_of_birth'],
                    'parent_contact_name' => $appData['personal_info']['parent_contact_name'],
                    'parent_contact_phone' => $appData['personal_info']['parent_contact_phone'],
                    'residence_country' => $appData['personal_info']['residence_country'],
                    'language' => $appData['personal_info']['language'],
                    'is_studied_in_saudi' => $appData['personal_info']['is_studied_in_saudi'],
                    'tahseeli_percentage' => $appData['personal_info']['tahseeli_percentage'],
                    'qudorat_percentage' => $appData['personal_info']['qudorat_percentage']
                ]);

                // Create application
                $application = ApplicantApplication::create([
                    'applicant_id' => $applicant->applicant_id,
                    'scholarship_id_1' => $appData['program_details']['scholarship_ids'][0] ?? null,
                    'scholarship_id_2' => $appData['program_details']['scholarship_ids'][1] ?? null,
                    'scholarship_id_3' => $appData['program_details']['scholarship_ids'][2] ?? null,
                    'specialization_1' => $appData['program_details']['specialization_1'],
                    'specialization_2' => $appData['program_details']['specialization_2'] ?? null,
                    'specialization_3' => $appData['program_details']['specialization_3'] ?? null,
                    'university_name' => $appData['program_details']['university_name'],
                    'country_name' => $appData['program_details']['country_name'],
                    'tuition_fee' => $appData['program_details']['tuition_fee'] ?? null,
                    'has_active_program' => $appData['program_details']['has_active_program'],
                    'current_semester_number' => $appData['program_details']['current_semester_number'] ?? null,
                    'cgpa' => $appData['program_details']['cgpa'] ?? null,
                    'cgpa_out_of' => $appData['program_details']['cgpa_out_of'] ?? null,
                    'terms_and_condition' => $appData['program_details']['terms_and_condition']
                ]);

                // Create qualifications (linked to applicant, not application)
                foreach ($appData['academic_info']['qualifications'] as $qualData) {
                    Qualification::create([
                        'applicant_id' => $applicant->applicant_id,
                        'qualification_type' => $qualData['qualification_type'],
                        'institute_name' => $qualData['institute_name'],
                        'year_of_graduation' => $qualData['year_of_graduation'],
                        'cgpa' => $qualData['cgpa'] ?? null,
                        'cgpa_out_of' => $qualData['cgpa_out_of'] ?? null,
                        'language_of_study' => $qualData['language_of_study'] ?? null,
                        'specialization' => $qualData['specialization'] ?? null,
                        'research_title' => $qualData['research_title'] ?? null
                    ]);
                }

                // Create initial status
                ApplicantApplicationStatus::create([
                    'application_id' => $application->application_id,
                    'status_name' => ApplicationStatus::ENROLLED->value,
                    'date' => now()->subDays(rand(1, 30)),
                    'comment' => 'Application submitted'
                ]);

                // Create current status if different from enrolled
                if ($appData['status'] !== ApplicationStatus::ENROLLED) {
                    ApplicantApplicationStatus::create([
                        'application_id' => $application->application_id,
                        'status_name' => $appData['status']->value,
                        'date' => now()->subDays(rand(1, 15)),
                        'comment' => $this->getStatusComment($appData['status'])
                    ]);
                }

                DB::commit();

                if ($this->command) {
                    $this->command->info("✅ Created application for {$applicant->en_name} (Status: {$appData['status']->value})");
                } else {
                    echo "✅ Created application for {$applicant->en_name} (Status: {$appData['status']->value})\n";
                }
            } catch (\Exception $e) {
                DB::rollBack();
                if ($this->command) {
                    $this->command->error("❌ Failed to create application " . ($index + 1) . ": " . $e->getMessage());
                } else {
                    echo "❌ Failed to create application " . ($index + 1) . ": " . $e->getMessage() . "\n";
                }
            }
        }

        if ($this->command) {
            $this->command->info('🎉 Application seeder completed!');
            $this->command->info('📧 All applicants have password: password123');
        } else {
            echo "🎉 Application seeder completed!\n";
            echo "📧 All applicants have password: password123\n";
        }
    }

    private function getStatusComment(ApplicationStatus $status): string
    {
        return match ($status) {
            ApplicationStatus::FIRST_APPROVAL => 'Application approved for first review',
            ApplicationStatus::SECOND_APPROVAL => 'Application approved for second review',
            ApplicationStatus::FINAL_APPROVAL => 'Application finally approved',
            ApplicationStatus::REJECTED => 'Application rejected after review',
            ApplicationStatus::MEETING_SCHEDULED => 'Meeting scheduled with applicant',
            default => 'Status updated'
        };
    }
}
