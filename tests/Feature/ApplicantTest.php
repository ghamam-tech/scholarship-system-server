<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Applicant;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    protected $admin;
    protected $adminToken;
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_admin_can_create_applicant_with_files()
    {
        $applicantData = [
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
            'email' => 'applicant@example.com',
            'password' => 'password123',
            'passport_copy_img' => UploadedFile::fake()->image('passport.jpg'),
            'volunteering_certificate_file' => UploadedFile::fake()->create('certificate.pdf'),
            'tahsili_file' => UploadedFile::fake()->create('tahsili.pdf'),
            'qudorat_file' => UploadedFile::fake()->create('qudorat.pdf'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/applicants', $applicantData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'user_id',
                    'email',
                    'role',
                ],
                'applicant' => [
                    'applicant_id',
                    'ar_name',
                    'en_name',
                    'passport_copy_img',
                    'volunteering_certificate_file',
                    'tahsili_file',
                    'qudorat_file',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'applicant@example.com',
            'role' => UserRole::APPLICANT->value,
        ]);

        $this->assertDatabaseHas('applicants', [
            'ar_name' => 'أحمد محمد',
            'en_name' => 'Ahmed Mohammed',
            'passport_number' => 'A1234567',
        ]);

        // Verify files were stored
        $this->assertNotNull($response->json('applicant.passport_copy_img'));
        $this->assertNotNull($response->json('applicant.volunteering_certificate_file'));
        $this->assertNotNull($response->json('applicant.tahsili_file'));
        $this->assertNotNull($response->json('applicant.qudorat_file'));
    }

    public function test_admin_can_view_all_applicants()
    {
        $user1 = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $user2 = User::factory()->create(['role' => UserRole::APPLICANT->value]);

        Applicant::factory()->create(['user_id' => $user1->user_id]);
        Applicant::factory()->create(['user_id' => $user2->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/applicants');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_admin_can_view_specific_applicant()
    {
        $user = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $applicant = Applicant::factory()->create(['user_id' => $user->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/applicants/{$applicant->applicant_id}");

        $response->assertStatus(200)
            ->assertJson([
                'applicant_id' => $applicant->applicant_id,
                'ar_name' => $applicant->ar_name,
                'en_name' => $applicant->en_name,
            ]);
    }

    public function test_admin_can_update_applicant()
    {
        $user = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $applicant = Applicant::factory()->create(['user_id' => $user->user_id]);

        $updateData = [
            'ar_name' => 'محمد أحمد',
            'en_name' => 'Mohammed Ahmed',
            'phone' => '+966501234569',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/applicants/{$applicant->applicant_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Applicant updated successfully',
            ]);

        $this->assertDatabaseHas('applicants', [
            'applicant_id' => $applicant->applicant_id,
            'ar_name' => 'محمد أحمد',
            'en_name' => 'Mohammed Ahmed',
            'phone' => '+966501234569',
        ]);
    }

    public function test_admin_can_delete_applicant()
    {
        $user = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $applicant = Applicant::factory()->create([
            'user_id' => $user->user_id,
            'passport_copy_img' => 'applicants/passport/test.jpg',
        ]);

        // Create fake file
        Storage::disk('s3')->put('applicants/passport/test.jpg', 'fake content');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/applicants/{$applicant->applicant_id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Applicant deleted successfully',
            ]);

        $this->assertDatabaseMissing('applicants', [
            'applicant_id' => $applicant->applicant_id,
        ]);

        // Verify file was deleted (check that the file path is no longer in database)
        $this->assertDatabaseMissing('applicants', [
            'applicant_id' => $applicant->applicant_id,
            'passport_copy_img' => 'applicants/passport/test.jpg',
        ]);
    }

    public function test_applicant_creation_requires_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/applicants', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'ar_name',
                'en_name',
                'nationality',
                'gender',
                'email',
            ]);
    }

    public function test_applicant_creation_validates_file_types()
    {
        $applicantData = [
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
            'email' => 'applicant@example.com',
            'password' => 'password123',
            'passport_copy_img' => UploadedFile::fake()->create('document.txt'), // Invalid file type
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/applicants', $applicantData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['passport_copy_img']);
    }
}
