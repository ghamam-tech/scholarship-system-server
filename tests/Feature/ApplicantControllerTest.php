<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Applicant;
use App\Models\Qualification;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $applicant;
    protected $token;
    protected $admin;
    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');

        // Create regular user/applicant
        $this->user = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $this->applicant = Applicant::factory()->create(['user_id' => $this->user->user_id]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        // Create admin user
        $this->admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
    }

    /**
     * Test completeProfile endpoint
     */
    public function test_complete_profile_with_valid_data()
    {
        $profileData = [
            'personal_info' => [
                'ar_name' => 'أحمد محمد',
                'en_name' => 'Ahmed Mohammed',
                'nationality' => 'Saudi',
                'gender' => 'male',
                'place_of_birth' => 'Riyadh',
                'phone' => '+966501234567',
                'passport_number' => 'A1234567',
                'date_of_birth' => '1995-01-01',
                'parent_contact_name' => 'Mohammed Ahmed',
                'parent_contact_phone' => '+966501234568',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
                'tahseeli_percentage' => 85.5,
                'qudorat_percentage' => 90.0,
            ],
            'academic_info' => [
                'qualifications' => [
                    [
                        'qualification_type' => 'bachelor',
                        'institute_name' => 'King Saud University',
                        'year_of_graduation' => 2020,
                        'cgpa' => 3.8,
                        'cgpa_out_of' => 4.0,
                        'language_of_study' => 'Arabic',
                        'specialization' => 'Computer Science',
                        'research_title' => 'Machine Learning Applications',
                        'document_file' => UploadedFile::fake()->create('bachelor_cert.pdf', 1000),
                    ],
                    [
                        'qualification_type' => 'high_school',
                        'institute_name' => 'Riyadh High School',
                        'year_of_graduation' => 2016,
                        'cgpa' => 95.5,
                        'cgpa_out_of' => 100,
                        'language_of_study' => 'Arabic',
                        'specialization' => 'Science',
                        'document_file' => UploadedFile::fake()->create('high_school_cert.pdf', 1000),
                    ],
                ],
            ],
            'passport_copy' => UploadedFile::fake()->create('passport.pdf', 1000),
            'personal_image' => UploadedFile::fake()->image('personal.jpg', 800, 600),
            'tahsili_file' => UploadedFile::fake()->create('tahsili.pdf', 1000),
            'qudorat_file' => UploadedFile::fake()->create('qudorat.pdf', 1000),
            'volunteering_certificate' => UploadedFile::fake()->create('volunteering.pdf', 1000),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/applicant/complete-profile', $profileData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'applicant' => [
                    'applicant_id',
                    'ar_name',
                    'en_name',
                    'nationality',
                    'gender',
                    'qualifications' => [
                        '*' => [
                            'qualification_id',
                            'qualification_type',
                            'institute_name',
                            'year_of_graduation',
                            'document_file',
                        ],
                    ],
                ],
            ]);

        // Verify data was saved
        $this->assertDatabaseHas('applicants', [
            'applicant_id' => $this->applicant->applicant_id,
            'ar_name' => 'أحمد محمد',
            'en_name' => 'Ahmed Mohammed',
            'passport_number' => 'A1234567',
        ]);

        // Verify qualifications were created
        $this->assertDatabaseHas('qualifications', [
            'applicant_id' => $this->applicant->applicant_id,
            'qualification_type' => 'bachelor',
            'institute_name' => 'King Saud University',
        ]);

        $this->assertDatabaseHas('qualifications', [
            'applicant_id' => $this->applicant->applicant_id,
            'qualification_type' => 'high_school',
            'institute_name' => 'Riyadh High School',
        ]);
    }

    public function test_complete_profile_validation_errors()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/applicant/complete-profile', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'personal_info.ar_name',
                'personal_info.en_name',
                'personal_info.nationality',
                'personal_info.gender',
                'personal_info.passport_number',
                'academic_info.qualifications',
                'passport_copy',
                'personal_image',
                'tahsili_file',
                'qudorat_file',
            ]);
    }

    public function test_complete_profile_requires_at_least_one_qualification()
    {
        $profileData = [
            'personal_info' => [
                'ar_name' => 'أحمد محمد',
                'en_name' => 'Ahmed Mohammed',
                'nationality' => 'Saudi',
                'gender' => 'male',
                'place_of_birth' => 'Riyadh',
                'phone' => '+966501234567',
                'passport_number' => 'A1234567',
                'date_of_birth' => '1995-01-01',
                'parent_contact_name' => 'Mohammed Ahmed',
                'parent_contact_phone' => '+966501234568',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
            ],
            'academic_info' => [
                'qualifications' => [], // Empty qualifications array
            ],
            'passport_copy' => UploadedFile::fake()->create('passport.pdf', 1000),
            'personal_image' => UploadedFile::fake()->image('personal.jpg', 800, 600),
            'tahsili_file' => UploadedFile::fake()->create('tahsili.pdf', 1000),
            'qudorat_file' => UploadedFile::fake()->create('qudorat.pdf', 1000),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/applicant/complete-profile', $profileData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['academic_info.qualifications']);
    }

    /**
     * Test updateProfile endpoint
     */
    public function test_update_profile_with_partial_data()
    {
        $updateData = [
            'personal_info' => [
                'ar_name' => 'محمد أحمد',
                'phone' => '+966501234569',
            ],
            'passport_copy' => UploadedFile::fake()->create('new_passport.pdf', 1000),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/v1/applicant/profile', $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'applicant' => [
                    'applicant_id',
                    'ar_name',
                    'phone',
                ],
            ]);

        $this->assertDatabaseHas('applicants', [
            'applicant_id' => $this->applicant->applicant_id,
            'ar_name' => 'محمد أحمد',
            'phone' => '+966501234569',
        ]);
    }

    public function test_update_profile_without_applicant()
    {
        // Create user without applicant
        $userWithoutApplicant = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $token = $userWithoutApplicant->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/applicant/profile', [
            'personal_info' => ['ar_name' => 'Test Name'],
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Applicant profile not found']);
    }

    /**
     * Test getProfile endpoint
     */
    public function test_get_profile_success()
    {
        // Create some qualifications for the applicant
        Qualification::factory()->create(['applicant_id' => $this->applicant->applicant_id]);
        Qualification::factory()->create(['applicant_id' => $this->applicant->applicant_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/applicant/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'applicant' => [
                    'applicant_id',
                    'ar_name',
                    'en_name',
                    'qualifications' => [
                        '*' => [
                            'qualification_id',
                            'qualification_type',
                            'institute_name',
                        ],
                    ],
                ],
            ]);

        $response->assertJsonCount(2, 'applicant.qualifications');
    }

    public function test_get_profile_not_found()
    {
        $userWithoutApplicant = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $token = $userWithoutApplicant->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/applicant/profile');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Applicant profile not found']);
    }

    /**
     * Test qualification CRUD operations
     */
    public function test_add_qualification()
    {
        $qualificationData = [
            'qualification_type' => 'master',
            'institute_name' => 'MIT',
            'year_of_graduation' => 2022,
            'cgpa' => 3.9,
            'cgpa_out_of' => 4.0,
            'language_of_study' => 'English',
            'specialization' => 'Artificial Intelligence',
            'research_title' => 'Deep Learning in Healthcare',
            'document_file' => UploadedFile::fake()->create('master_cert.pdf', 1000),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/applicant/qualifications', $qualificationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'qualification' => [
                    'qualification_id',
                    'qualification_type',
                    'institute_name',
                    'year_of_graduation',
                    'document_file',
                ],
            ]);

        $this->assertDatabaseHas('qualifications', [
            'applicant_id' => $this->applicant->applicant_id,
            'qualification_type' => 'master',
            'institute_name' => 'MIT',
        ]);
    }

    public function test_update_qualification()
    {
        $qualification = Qualification::factory()->create(['applicant_id' => $this->applicant->applicant_id]);

        $updateData = [
            'institute_name' => 'Updated Institute',
            'cgpa' => 3.95,
            'document_file' => UploadedFile::fake()->create('updated_cert.pdf', 1000),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/v1/applicant/qualifications/{$qualification->qualification_id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'qualification' => [
                    'qualification_id',
                    'institute_name',
                    'cgpa',
                ],
            ]);

        $this->assertDatabaseHas('qualifications', [
            'qualification_id' => $qualification->qualification_id,
            'institute_name' => 'Updated Institute',
            'cgpa' => 3.95,
        ]);
    }

    public function test_delete_qualification()
    {
        $qualification = Qualification::factory()->create([
            'applicant_id' => $this->applicant->applicant_id,
            'document_file' => 'test/path/document.pdf',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/applicant/qualifications/{$qualification->qualification_id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Qualification deleted successfully']);

        $this->assertDatabaseMissing('qualifications', [
            'qualification_id' => $qualification->qualification_id,
        ]);
    }

    public function test_update_qualification_not_found()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/v1/applicant/qualifications/99999', [
            'institute_name' => 'Test Institute',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_qualification_not_found()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/v1/applicant/qualifications/99999');

        $response->assertStatus(404);
    }

    /**
     * Test admin endpoints
     */
    public function test_admin_can_view_all_applicants()
    {
        // Create additional applicants
        $user2 = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $user3 = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        Applicant::factory()->create(['user_id' => $user2->user_id]);
        Applicant::factory()->create(['user_id' => $user3->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/applicants');

        $response->assertStatus(200)
            ->assertJsonCount(3); // Should return all 3 applicants
    }

    public function test_admin_can_view_specific_applicant()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/applicants/{$this->applicant->applicant_id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'applicant_id',
                'ar_name',
                'en_name',
                'user' => [
                    'user_id',
                    'email',
                    'role',
                ],
                'qualifications',
                'applications',
            ]);
    }

    public function test_admin_can_delete_applicant()
    {
        // Set up files for deletion
        $this->applicant->update([
            'passport_copy_img' => 'test/passport.jpg',
            'personal_image' => 'test/personal.jpg',
        ]);

        // Create fake files
        Storage::disk('s3')->put('test/passport.jpg', 'fake content');
        Storage::disk('s3')->put('test/personal.jpg', 'fake content');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/applicants/{$this->applicant->applicant_id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Applicant deleted successfully']);

        $this->assertDatabaseMissing('applicants', [
            'applicant_id' => $this->applicant->applicant_id,
        ]);
    }

    /**
     * Test authentication and authorization
     */
    public function test_unauthenticated_user_cannot_access_endpoints()
    {
        $response = $this->postJson('/api/v1/applicant/complete-profile', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/applicant/profile');
        $response->assertStatus(401);

        $response = $this->putJson('/api/v1/applicant/profile', []);
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_access_admin_endpoints()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/applicants');

        $response->assertStatus(403); // Assuming you have role middleware
    }

    /**
     * Test file upload validation
     */
    public function test_file_upload_validation()
    {
        $profileData = [
            'personal_info' => [
                'ar_name' => 'أحمد محمد',
                'en_name' => 'Ahmed Mohammed',
                'nationality' => 'Saudi',
                'gender' => 'male',
                'place_of_birth' => 'Riyadh',
                'phone' => '+966501234567',
                'passport_number' => 'A1234567',
                'date_of_birth' => '1995-01-01',
                'parent_contact_name' => 'Mohammed Ahmed',
                'parent_contact_phone' => '+966501234568',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
            ],
            'academic_info' => [
                'qualifications' => [
                    [
                        'qualification_type' => 'bachelor',
                        'institute_name' => 'King Saud University',
                        'year_of_graduation' => 2020,
                        'document_file' => UploadedFile::fake()->create('cert.txt', 1000), // Invalid file type
                    ],
                ],
            ],
            'passport_copy' => UploadedFile::fake()->create('passport.txt', 1000), // Invalid file type
            'personal_image' => UploadedFile::fake()->create('personal.txt', 1000), // Invalid file type
            'tahsili_file' => UploadedFile::fake()->create('tahsili.pdf', 1000),
            'qudorat_file' => UploadedFile::fake()->create('qudorat.pdf', 1000),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/applicant/complete-profile', $profileData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'academic_info.qualifications.0.document_file',
                'passport_copy',
                'personal_image',
            ]);
    }

    public function test_file_size_validation()
    {
        $profileData = [
            'personal_info' => [
                'ar_name' => 'أحمد محمد',
                'en_name' => 'Ahmed Mohammed',
                'nationality' => 'Saudi',
                'gender' => 'male',
                'place_of_birth' => 'Riyadh',
                'phone' => '+966501234567',
                'passport_number' => 'A1234567',
                'date_of_birth' => '1995-01-01',
                'parent_contact_name' => 'Mohammed Ahmed',
                'parent_contact_phone' => '+966501234568',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
            ],
            'academic_info' => [
                'qualifications' => [
                    [
                        'qualification_type' => 'bachelor',
                        'institute_name' => 'King Saud University',
                        'year_of_graduation' => 2020,
                        'document_file' => UploadedFile::fake()->create('cert.pdf', 15000), // Too large
                    ],
                ],
            ],
            'passport_copy' => UploadedFile::fake()->create('passport.pdf', 1000),
            'personal_image' => UploadedFile::fake()->image('personal.jpg', 800, 600),
            'tahsili_file' => UploadedFile::fake()->create('tahsili.pdf', 1000),
            'qudorat_file' => UploadedFile::fake()->create('qudorat.pdf', 1000),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/applicant/complete-profile', $profileData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['academic_info.qualifications.0.document_file']);
    }

    /**
     * Test passport number uniqueness
     */
    public function test_passport_number_uniqueness()
    {
        // Create another applicant with the same passport number
        $user2 = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $applicant2 = Applicant::factory()->create([
            'user_id' => $user2->user_id,
            'passport_number' => 'A1234567',
        ]);

        $profileData = [
            'personal_info' => [
                'ar_name' => 'أحمد محمد',
                'en_name' => 'Ahmed Mohammed',
                'nationality' => 'Saudi',
                'gender' => 'male',
                'place_of_birth' => 'Riyadh',
                'phone' => '+966501234567',
                'passport_number' => 'A1234567', // Same passport number
                'date_of_birth' => '1995-01-01',
                'parent_contact_name' => 'Mohammed Ahmed',
                'parent_contact_phone' => '+966501234568',
                'residence_country' => 'Saudi Arabia',
                'language' => 'Arabic',
                'is_studied_in_saudi' => true,
            ],
            'academic_info' => [
                'qualifications' => [
                    [
                        'qualification_type' => 'bachelor',
                        'institute_name' => 'King Saud University',
                        'year_of_graduation' => 2020,
                        'document_file' => UploadedFile::fake()->create('cert.pdf', 1000),
                    ],
                ],
            ],
            'passport_copy' => UploadedFile::fake()->create('passport.pdf', 1000),
            'personal_image' => UploadedFile::fake()->image('personal.jpg', 800, 600),
            'tahsili_file' => UploadedFile::fake()->create('tahsili.pdf', 1000),
            'qudorat_file' => UploadedFile::fake()->create('qudorat.pdf', 1000),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/applicant/complete-profile', $profileData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['personal_info.passport_number']);
    }
}
