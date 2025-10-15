<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    public function test_health_check_endpoint()
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'app',
                'version',
                'status',
            ])
            ->assertJson([
                'version' => 'v1',
                'status' => 'ok',
            ]);
    }

    public function test_api_fallback_returns_404()
    {
        $response = $this->getJson('/api/v1/nonexistent-endpoint');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Not Found',
            ]);
    }
}
