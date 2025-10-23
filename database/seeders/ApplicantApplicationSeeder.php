<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Applicant;
use App\Models\Qualification;
use App\Models\ApplicantApplication;
use App\Models\UserStatus;
use App\Models\Scholarship;
use App\Enums\UserRole;
use App\Enums\ApplicationStatus;

class ApplicantApplicationSeeder extends Seeder
{
    public function run(): void
    {

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
            ],
            [
                'email' => 'lina.haddad@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'Lina Haddad',
                    'en_name' => 'Lina Haddad',
                    'nationality' => 'Jordanian',
                    'gender' => 'female',
                    'place_of_birth' => 'Amman',
                    'phone' => '+962700112233',
                    'passport_number' => 'F67890123',
                    'date_of_birth' => '2002-05-18',
                    'parent_contact_name' => 'Hani Haddad',
                    'parent_contact_phone' => '+962700221133',
                    'residence_country' => 'Jordan',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => false,
                    'tahseeli_percentage' => null,
                    'qudorat_percentage' => null,
                ]
            ],
            [
                'email' => 'noor.abbas@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'Noor Abbas',
                    'en_name' => 'Noor Abbas',
                    'nationality' => 'Saudi',
                    'gender' => 'female',
                    'place_of_birth' => 'Taif',
                    'phone' => '+966505551234',
                    'passport_number' => 'G78901234',
                    'date_of_birth' => '2001-09-12',
                    'parent_contact_name' => 'Abbas Saleh',
                    'parent_contact_phone' => '+966505551235',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 91.4,
                    'qudorat_percentage' => 89.7,
                ]
            ],
            [
                'email' => 'hassan.rami@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'Hassan Rami',
                    'en_name' => 'Hassan Rami',
                    'nationality' => 'Lebanese',
                    'gender' => 'male',
                    'place_of_birth' => 'Beirut',
                    'phone' => '+96170123456',
                    'passport_number' => 'H89012345',
                    'date_of_birth' => '1999-04-07',
                    'parent_contact_name' => 'Rami Hassan',
                    'parent_contact_phone' => '+96170123457',
                    'residence_country' => 'Lebanon',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => false,
                    'tahseeli_percentage' => null,
                    'qudorat_percentage' => null,
                ]
            ],
            [
                'email' => 'reem.altamimi@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'Reem Al Tamimi',
                    'en_name' => 'Reem Al Tamimi',
                    'nationality' => 'Saudi',
                    'gender' => 'female',
                    'place_of_birth' => 'Riyadh',
                    'phone' => '+966504223344',
                    'passport_number' => 'I90123456',
                    'date_of_birth' => '2002-02-02',
                    'parent_contact_name' => 'Talal Al Tamimi',
                    'parent_contact_phone' => '+966504223355',
                    'residence_country' => 'Saudi Arabia',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => true,
                    'tahseeli_percentage' => 88.6,
                    'qudorat_percentage' => 86.9,
                ]
            ],
            [
                'email' => 'yousef.qassim@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'Yousef Qassim',
                    'en_name' => 'Yousef Qassim',
                    'nationality' => 'Palestinian',
                    'gender' => 'male',
                    'place_of_birth' => 'Nablus',
                    'phone' => '+970592001122',
                    'passport_number' => 'J01234567',
                    'date_of_birth' => '2000-12-30',
                    'parent_contact_name' => 'Qassim Yousef',
                    'parent_contact_phone' => '+970592001133',
                    'residence_country' => 'Palestine',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => false,
                    'tahseeli_percentage' => null,
                    'qudorat_percentage' => null,
                ]
            ],
            [
                'email' => 'mona.salem@example.com',
                'password' => 'password123',
                'applicant_data' => [
                    'ar_name' => 'Mona Salem',
                    'en_name' => 'Mona Salem',
                    'nationality' => 'Egyptian',
                    'gender' => 'female',
                    'place_of_birth' => 'Alexandria',
                    'phone' => '+201115556677',
                    'passport_number' => 'K12345678',
                    'date_of_birth' => '1998-08-21',
                    'parent_contact_name' => 'Salem Fathi',
                    'parent_contact_phone' => '+201115556688',
                    'residence_country' => 'Egypt',
                    'language' => 'Arabic',
                    'is_studied_in_saudi' => false,
                    'tahseeli_percentage' => null,
                    'qudorat_percentage' => null,
                ]
            ],
        ];

        foreach ($applicants as $applicantData) {
            // 1) Create user
            $user = User::create([
                'email' => $applicantData['email'],
                'password' => Hash::make($applicantData['password']),
                'role' => UserRole::APPLICANT->value,
            ]);

            // 2) Create applicant profile linked to the user
            $applicant = Applicant::create(array_merge(
                $applicantData['applicant_data'],
                ['user_id' => $user->user_id]
            ));

            // 3) Create qualifications (NOW linked to USER)
            $this->createQualifications($user, $applicant);

            // 4) Create single application per applicant
            $this->createApplications($applicant);
        }

        $this->command->info(count($applicants) . " applicants created with qualifications and single applications.");
        $this->command->info("All seeded applicants have password: password123");
    }

    /**
     * NOTE: changed signature to receive User as well.
     * Qualifications now belong to USER via user_id.
     * We also store document_file as a PATH (no full URL).
     */
    private function createQualifications(User $user, Applicant $applicant): void
    {
        $uid = $user->user_id;
        $qualifications = [];

        // High school – everyone has one
        $qualifications[] = [
            'user_id' => $uid,                     // 🔁 was applicant_id
            'qualification_type' => 'high_school',
            'institute_name' => $applicant->place_of_birth . ' High School',
            'year_of_graduation' => 2019,
            'cgpa' => rand(34, 39) / 10,
            'cgpa_out_of' => 4.0,
            'language_of_study' => 'Arabic',
            'specialization' => 'Science',
            'research_title' => null,
            // store PATH (object key) not a URL
            'document_file' => "seed/users/{$uid}/qualifications/high_school_{$uid}.pdf",
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Bachelor – ~50%
        if (rand(0, 1)) {
            $qualifications[] = [
                'user_id' => $uid,
                'qualification_type' => 'bachelor',
                'institute_name' => 'King Saud University',
                'year_of_graduation' => 2023,
                'cgpa' => rand(34, 39) / 10,
                'cgpa_out_of' => 4.0,
                'language_of_study' => 'Arabic',
                'specialization' => 'Computer Science',
                'research_title' => 'Software Engineering Principles',
                'document_file' => "seed/users/{$uid}/qualifications/bachelor_{$uid}.pdf",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Master – ~25%
        if (rand(0, 3) === 0) {
            $qualifications[] = [
                'user_id' => $uid,
                'qualification_type' => 'master',
                'institute_name' => 'King Abdulaziz University',
                'year_of_graduation' => 2024,
                'cgpa' => rand(36, 40) / 10,
                'cgpa_out_of' => 4.0,
                'language_of_study' => 'English',
                'specialization' => 'Artificial Intelligence',
                'research_title' => 'Machine Learning Applications in Healthcare',
                'document_file' => "seed/users/{$uid}/qualifications/master_{$uid}.pdf",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Diploma – ~33%
        if (rand(0, 2) === 0) {
            $qualifications[] = [
                'user_id' => $uid,
                'qualification_type' => 'diploma',
                'institute_name' => 'Technical College ' . $applicant->place_of_birth,
                'year_of_graduation' => 2020,
                'cgpa' => rand(35, 38) / 10,
                'cgpa_out_of' => 4.0,
                'language_of_study' => 'Arabic',
                'specialization' => 'Information Technology',
                'research_title' => null,
                'document_file' => "seed/users/{$uid}/qualifications/diploma_{$uid}.pdf",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Qualification::insert($qualifications);
    }

    private function createApplications(Applicant $applicant): void
    {
        static $scholarshipIds = null;

        if ($scholarshipIds === null) {
            $scholarshipIds = Scholarship::pluck('scholarship_id')->all();
        }

        if (empty($scholarshipIds)) {
            $this->command?->warn('No scholarships found. Skipping application creation for applicants.');
            return;
        }

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
        $scholarshipId = $scholarshipIds[array_rand($scholarshipIds)];

        $application = ApplicantApplication::create([
            'applicant_id' => $applicant->applicant_id,
            'scholarship_id' => $scholarshipId,
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
            'offer_letter_file' => "seed/applicants/{$applicant->applicant_id}/applications/offer_letter_{$applicant->applicant_id}.pdf",
            'created_at' => now()->subDays(rand(1, 30)),
            'updated_at' => now(),
        ]);

        $this->createApplicationStatuses($application);
    }

    private function createApplicationStatuses(ApplicantApplication $application): void
    {
        $userId = $application->applicant->user_id;
        $statuses = [];
        $currentDate = $application->created_at->copy();

        $statuses[] = [
            'user_id' => $userId,
            'status_name' => ApplicationStatus::ENROLLED->value,
            'date' => $currentDate,
            'comment' => 'Application submitted successfully',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ];

        $possible = [
            ApplicationStatus::FIRST_APPROVAL->value,
            ApplicationStatus::SECOND_APPROVAL->value,
            ApplicationStatus::FINAL_APPROVAL->value,
            ApplicationStatus::REJECTED->value,
        ];

        foreach ($possible as $status) {
            if (rand(0, 100) > 70)
                break;

            $currentDate = $currentDate->copy()->addDays(rand(2, 7));

            $statuses[] = [
                'user_id' => $userId,
                'status_name' => $status,
                'date' => $currentDate,
                'comment' => $this->getStatusComment($status),
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ];

            if ($status === ApplicationStatus::REJECTED->value)
                break;
        }

        UserStatus::insert($statuses);
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

