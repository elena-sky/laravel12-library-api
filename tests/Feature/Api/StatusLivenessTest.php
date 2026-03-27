<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class StatusLivenessTest extends TestCase
{
    public function test_liveness_returns_unified_success_shape(): void
    {
        $this->getJson('/api/v1/status/liveness')
            ->assertOk()
            ->assertExactJson([
                'data' => ['status' => 'ok'],
                'message' => 'Liveness check passed',
            ]);
    }
}
