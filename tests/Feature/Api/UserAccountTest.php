<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
