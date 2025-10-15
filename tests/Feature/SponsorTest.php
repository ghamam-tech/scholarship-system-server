<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Sponsor;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SponsorTest extends TestCase
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

    public function test_admin_can_create_sponsor()
    {
        $sponsorData = [
            'name' => 'Test Sponsor Organization',
            'email' => 'sponsor@example.com',
            'password' => 'password123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/sponsors', $sponsorData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'user_id',
                    'email',
                    'role',
                ],
                'sponsor' => [
                    'sponsor_id',
                    'name',
                    'user_id',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'sponsor@example.com',
            'role' => UserRole::SPONSOR->value,
        ]);

        $this->assertDatabaseHas('sponsors', [
            'name' => 'Test Sponsor Organization',
        ]);
    }

    public function test_admin_can_view_all_sponsors()
    {
        $user1 = User::factory()->create(['role' => UserRole::SPONSOR->value]);
        $user2 = User::factory()->create(['role' => UserRole::SPONSOR->value]);

        Sponsor::factory()->create(['user_id' => $user1->user_id]);
        Sponsor::factory()->create(['user_id' => $user2->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/sponsors');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_admin_can_view_specific_sponsor()
    {
        $user = User::factory()->create(['role' => UserRole::SPONSOR->value]);
        $sponsor = Sponsor::factory()->create(['user_id' => $user->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/sponsors/{$sponsor->sponsor_id}");

        $response->assertStatus(200)
            ->assertJson([
                'sponsor_id' => $sponsor->sponsor_id,
                'name' => $sponsor->name,
            ]);
    }

    public function test_admin_can_update_sponsor()
    {
        $user = User::factory()->create(['role' => UserRole::SPONSOR->value]);
        $sponsor = Sponsor::factory()->create(['user_id' => $user->user_id]);

        $updateData = [
            'name' => 'Updated Sponsor Name',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/sponsors/{$sponsor->sponsor_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Sponsor updated successfully',
            ]);

        $this->assertDatabaseHas('sponsors', [
            'sponsor_id' => $sponsor->sponsor_id,
            'name' => 'Updated Sponsor Name',
        ]);
    }

    public function test_admin_can_delete_sponsor()
    {
        $user = User::factory()->create(['role' => UserRole::SPONSOR->value]);
        $sponsor = Sponsor::factory()->create(['user_id' => $user->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/sponsors/{$sponsor->sponsor_id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Sponsor deleted successfully',
            ]);

        $this->assertDatabaseMissing('sponsors', [
            'sponsor_id' => $sponsor->sponsor_id,
        ]);
    }

    public function test_sponsor_creation_requires_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/sponsors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
            ]);
    }

    public function test_sponsor_email_must_be_unique()
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $sponsorData = [
            'name' => 'Test Sponsor',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/sponsors', $sponsorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
