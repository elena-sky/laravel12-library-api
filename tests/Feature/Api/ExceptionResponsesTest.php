<?php

namespace Tests\Feature\Api;

use App\Exceptions\ResourceConflictException;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExceptionResponsesTest extends TestCase
{
    public function test_validation_exception_returns_unified_json(): void
    {
        Route::middleware('api')->post('/api/v1/_test_validation', function (Request $request) {
            $request->validate(['email' => 'required|email']);

            return ApiResponse::success();
        });

        $this->postJson('/api/v1/_test_validation', [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors']);
    }

    public function test_unknown_api_route_returns_404_json(): void
    {
        $this->getJson('/api/v1/does-not-exist-'.uniqid())
            ->assertNotFound()
            ->assertJson(['message' => 'Resource not found']);
    }

    public function test_unexpected_exception_does_not_leak_details(): void
    {
        Route::middleware('api')->get('/api/v1/_test_server', function () {
            throw new \RuntimeException('internal_secret_do_not_leak');
        });

        $this->getJson('/api/v1/_test_server')
            ->assertStatus(500)
            ->assertJson(['message' => 'Server error'])
            ->assertJsonMissing(['internal_secret_do_not_leak']);
    }

    public function test_api_exception_conflict_returns_json(): void
    {
        Route::middleware('api')->get('/api/v1/_test_conflict', function () {
            throw new ResourceConflictException('Book is not available for rent');
        });

        $this->getJson('/api/v1/_test_conflict')
            ->assertStatus(409)
            ->assertJson(['message' => 'Book is not available for rent']);
    }

    public function test_authentication_exception_returns_401_json(): void
    {
        Route::middleware('api')->get('/api/v1/_test_auth', function () {
            throw new AuthenticationException;
        });

        $this->getJson('/api/v1/_test_auth')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated']);
    }
}
