<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // API tests use Bearer tokens only; avoid web-session auth resolving before the token guard.
        $this->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
    }
}
