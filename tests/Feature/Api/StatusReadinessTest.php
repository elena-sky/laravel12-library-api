<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class StatusReadinessTest extends TestCase
{
    public function test_readiness_returns_unified_success_when_database_is_up(): void
    {
        $this->getJson('/api/v1/status/readiness')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'status' => 'ok',
                    'database' => 'ok',
                ],
                'message' => 'Readiness check passed',
            ]);
    }
}
