<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Country;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryTest extends TestCase
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

    public function test_admin_can_create_country()
    {
        $countryData = [
            'country_name' => 'Saudi Arabia',
            'country_code' => 'SA',
            'is_active' => true,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/countries', $countryData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'country' => [
                    'country_id',
                    'country_name',
                    'country_code',
                    'is_active',
                ],
            ]);

        $this->assertDatabaseHas('countries', [
            'country_name' => 'Saudi Arabia',
            'country_code' => 'SA',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_all_countries()
    {
        Country::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/countries');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_admin_can_view_specific_country()
    {
        $country = Country::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/countries/{$country->country_id}");

        $response->assertStatus(200)
            ->assertJson([
                'country_id' => $country->country_id,
                'country_name' => $country->country_name,
                'country_code' => $country->country_code,
            ]);
    }

    public function test_admin_can_update_country()
    {
        $country = Country::factory()->create();

        $updateData = [
            'country_name' => 'Updated Country Name',
            'is_active' => false,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/countries/{$country->country_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Country updated successfully',
            ]);

        $this->assertDatabaseHas('countries', [
            'country_id' => $country->country_id,
            'country_name' => 'Updated Country Name',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_country()
    {
        $country = Country::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/countries/{$country->country_id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Country deleted successfully',
            ]);

        $this->assertDatabaseMissing('countries', [
            'country_id' => $country->country_id,
        ]);
    }

    public function test_country_creation_requires_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/countries', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'country_name',
                'country_code',
            ]);
    }

    public function test_country_code_must_be_unique()
    {
        Country::factory()->create(['country_code' => 'SA']);

        $countryData = [
            'country_name' => 'Another Country',
            'country_code' => 'SA',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/countries', $countryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    }
}
