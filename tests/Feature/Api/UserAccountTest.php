<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class UserAccountTest extends TestCase
{
    use RefreshDatabase;

    private const string SAFE_PASSWORD = 'Correct-Horse-Battery-Staple-99';

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => self::SAFE_PASSWORD,
            'password_confirmation' => self::SAFE_PASSWORD,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Registration successful')
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com', 'name' => 'Ada Lovelace']);
        $response->assertJsonMissingPath('data.user.password');
        $response->assertJsonMissingPath('data.password');
    }

    public function test_registration_validation_requires_core_fields(): void
    {
        $this->postJson('/api/v1/register', [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $this->postJson('/api/v1/register', [
            'name' => 'Other',
            'email' => 'dup@example.com',
            'password' => self::SAFE_PASSWORD,
            'password_confirmation' => self::SAFE_PASSWORD,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed');
    }

    public function test_registered_password_is_hashed_in_database(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Hashed User',
            'email' => 'hashed@example.com',
            'password' => self::SAFE_PASSWORD,
            'password_confirmation' => self::SAFE_PASSWORD,
        ])->assertCreated();

        $user = User::query()->where('email', 'hashed@example.com')->firstOrFail();
        $this->assertNotSame(self::SAFE_PASSWORD, $user->getAuthPassword());
        $this->assertTrue(Hash::check(self::SAFE_PASSWORD, $user->getAuthPassword()));
    }

    public function test_authenticated_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.password');
    }

    public function test_guest_cannot_view_profile(): void
    {
        $this->getJson('/api/v1/user')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated']);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->patchJson('/api/v1/user', ['name' => 'X'])
            ->assertUnauthorized();
    }

    public function test_guest_cannot_update_password(): void
    {
        $this->putJson('/api/v1/user/password', [
            'current_password' => 'a',
            'password' => self::SAFE_PASSWORD,
            'password_confirmation' => self::SAFE_PASSWORD,
        ])
            ->assertUnauthorized();
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/user', [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Current user profile updated')
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.email', 'new@example.com');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'new@example.com']);
    }

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        $plain = self::SAFE_PASSWORD;
        $user = User::factory()->create(['password' => $plain]);
        $newPlain = 'Another-Strong-Password-88';

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/user/password', [
                'current_password' => $plain,
                'password' => $newPlain,
                'password_confirmation' => $newPlain,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Password updated successfully');

        $user->refresh();
        $this->assertTrue(Hash::check($newPlain, $user->getAuthPassword()));
    }

    public function test_password_update_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => self::SAFE_PASSWORD]);
        $newPlain = 'Another-Strong-Password-88';

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/user/password', [
                'current_password' => 'wrong-password',
                'password' => $newPlain,
                'password_confirmation' => $newPlain,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed');
    }

    public function test_user_can_login_successfully(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => self::SAFE_PASSWORD,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login@example.com',
            'password' => self::SAFE_PASSWORD,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                    'token',
                    'token_type',
                ],
            ]);

        $response->assertJsonMissingPath('data.user.password');
        $response->assertJsonMissingPath('data.password');
    }

    public function test_login_rejects_invalid_credentials_for_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'u@example.com',
            'password' => self::SAFE_PASSWORD,
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'u@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_login_rejects_invalid_credentials_for_unknown_email(): void
    {
        $this->postJson('/api/v1/login', [
            'email' => 'missing@example.com',
            'password' => self::SAFE_PASSWORD,
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_login_validation_error_for_invalid_email(): void
    {
        $this->postJson('/api/v1/login', [
            'email' => 'not-valid-email',
            'password' => 'secret',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed');
    }

    public function test_logout_revokes_current_token(): void
    {
        User::factory()->create([
            'email' => 'out@example.com',
            'password' => self::SAFE_PASSWORD,
        ]);

        $token = $this->postJson('/api/v1/login', [
            'email' => 'out@example.com',
            'password' => self::SAFE_PASSWORD,
        ])->assertOk()->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout successful')
            ->assertJsonMissingPath('data');

        $this->assertNull(PersonalAccessToken::findToken($token));

        // Same PHPUnit app instance serves multiple HTTP calls; clear resolved auth between them.
        $this->flushHeaders();
        $this->app->make('auth')->forgetGuards();

        $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated');
    }

    public function test_guest_cannot_logout(): void
    {
        $this->postJson('/api/v1/logout')
            ->assertUnauthorized();
    }
}
