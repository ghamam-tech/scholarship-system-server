<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Country;
use App\Models\University;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UniversityTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    protected $admin;
    protected $adminToken;
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_admin_can_create_university()
    {
        $country = Country::factory()->create();

        $universityData = [
            'university_name' => 'King Saud University',
            'city' => 'Riyadh',
            'is_active' => true,
            'country_id' => $country->country_id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/universities', $universityData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'university' => [
                    'university_id',
                    'university_name',
                    'city',
                    'is_active',
                    'country_id',
                ],
            ]);

        $this->assertDatabaseHas('universities', [
            'university_name' => 'King Saud University',
            'city' => 'Riyadh',
            'country_id' => $country->country_id,
        ]);
    }

    public function test_admin_can_view_all_universities()
    {
        $country = Country::factory()->create();
        University::factory()->count(3)->create(['country_id' => $country->country_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/universities');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_admin_can_view_specific_university()
    {
        $country = Country::factory()->create();
        $university = University::factory()->create(['country_id' => $country->country_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/universities/{$university->university_id}");

        $response->assertStatus(200)
            ->assertJson([
                'university_id' => $university->university_id,
                'university_name' => $university->university_name,
                'city' => $university->city,
            ]);
    }

    public function test_admin_can_update_university()
    {
        $country = Country::factory()->create();
        $university = University::factory()->create(['country_id' => $country->country_id]);

        $updateData = [
            'university_name' => 'Updated University Name',
            'city' => 'Updated City',
            'is_active' => false,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/universities/{$university->university_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'University updated successfully',
            ]);

        $this->assertDatabaseHas('universities', [
            'university_id' => $university->university_id,
            'university_name' => 'Updated University Name',
            'city' => 'Updated City',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_university()
    {
        $country = Country::factory()->create();
        $university = University::factory()->create(['country_id' => $country->country_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/universities/{$university->university_id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'University deleted successfully',
            ]);

        $this->assertDatabaseMissing('universities', [
            'university_id' => $university->university_id,
        ]);
    }

    public function test_university_creation_requires_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/universities', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'university_name',
                'city',
                'country_id',
            ]);
    }

    public function test_university_requires_valid_country()
    {
        $universityData = [
            'university_name' => 'Test University',
            'city' => 'Test City',
            'country_id' => 999, // Non-existent country
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/universities', $universityData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_id']);
    }
}
