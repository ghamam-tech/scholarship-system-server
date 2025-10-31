<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityApplicationTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $this->adminToken = $admin->createToken('test-token')->plainTextToken;
    }

    public function test_get_opportunity_applications_returns_404_when_opportunity_not_found(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/admin/opportunities/999999/applications');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Opportunity not found']);
    }

    public function test_get_opportunity_applications_returns_200_with_empty_list(): void
    {
        $opportunity = Opportunity::create([
            'title' => 'Test Opp',
            'opportunity_status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/admin/opportunities/{$opportunity->opportunity_id}/applications");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'opportunity' => ['opportunity_id', 'title'],
                'applications',
            ]);
    }

    public function test_update_status_returns_404_when_opportunity_not_found(): void
    {
        $payload = [
            'applications' => [
                ['application_id' => 'opp_0000001', 'status' => 'accepted']
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->patchJson('/api/v1/admin/opportunities/999999/applications/status', $payload);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Opportunity not found']);
    }
}
