<?php

namespace Tests\Feature\Api;

use App\Models\BookRent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    private const string SAFE_PASSWORD = 'Correct-Horse-Battery-Staple-99';

    public function test_guest_cannot_list_users(): void
    {
        $this->getJson('/api/v1/users')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated']);
    }

    public function test_guest_cannot_create_user(): void
    {
        $this->postJson('/api/v1/users', [
            'name' => 'X',
            'email' => 'x@example.com',
            'password' => self::SAFE_PASSWORD,
            'password_confirmation' => self::SAFE_PASSWORD,
        ])->assertUnauthorized();
    }

    public function test_guest_cannot_show_user(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/v1/users/'.$user->id)->assertUnauthorized();
    }

    public function test_guest_cannot_update_user(): void
    {
        $user = User::factory()->create();

        $this->patchJson('/api/v1/users/'.$user->id, ['name' => 'Y'])->assertUnauthorized();
    }

    public function test_guest_cannot_delete_user(): void
    {
        $user = User::factory()->create();

        $this->deleteJson('/api/v1/users/'.$user->id)->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_users_paginated(): void
    {
        User::factory()->count(3)->create();
        $actor = User::factory()->create();

        $response = $this->actingAs($actor, 'sanctum')->getJson('/api/v1/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                ],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertGreaterThanOrEqual(4, $response->json('meta.total'));
    }

    public function test_list_users_per_page_validation_max(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->getJson('/api/v1/users?per_page=200')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_authenticated_user_can_create_user(): void
    {
        $actor = User::factory()->create();

        $response = $this->actingAs($actor, 'sanctum')->postJson('/api/v1/users', [
            'name' => 'Managed User',
            'email' => 'managed@example.com',
            'password' => self::SAFE_PASSWORD,
            'password_confirmation' => self::SAFE_PASSWORD,
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'User created')
            ->assertJsonPath('data.email', 'managed@example.com');

        $this->assertDatabaseHas('users', ['email' => 'managed@example.com', 'name' => 'Managed User']);
    }

    public function test_store_user_validation_errors(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->postJson('/api/v1/users', [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_store_user_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $actor = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'X',
                'email' => 'taken@example.com',
                'password' => self::SAFE_PASSWORD,
                'password_confirmation' => self::SAFE_PASSWORD,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_show_another_user(): void
    {
        $actor = User::factory()->create();
        $other = User::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($actor, 'sanctum')
            ->getJson('/api/v1/users/'.$other->id)
            ->assertOk()
            ->assertJsonPath('data.id', $other->id)
            ->assertJsonPath('data.email', 'other@example.com')
            ->assertJsonMissingPath('data.password');
    }

    public function test_authenticated_user_can_patch_user_partially(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['name' => 'Old', 'email' => 'target@example.com']);

        $this->actingAs($actor, 'sanctum')
            ->patchJson('/api/v1/users/'.$target->id, ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('message', 'User updated')
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.email', 'target@example.com');
    }

    public function test_update_user_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'a@example.com']);
        $target = User::factory()->create(['email' => 'b@example.com']);
        $actor = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->patchJson('/api/v1/users/'.$target->id, ['email' => 'a@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_delete_another_user(): void
    {
        $actor = User::factory()->create();
        $victim = User::factory()->create();

        $this->actingAs($actor, 'sanctum')
            ->deleteJson('/api/v1/users/'.$victim->id)
            ->assertOk()
            ->assertJsonPath('message', 'User deleted');

        $this->assertDatabaseMissing('users', ['id' => $victim->id]);
    }

    public function test_cannot_delete_own_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/users/'.$user->id)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Cannot delete your own account');
    }

    public function test_cannot_delete_user_with_rentals(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        BookRent::factory()->create(['user_id' => $target->id]);

        $this->actingAs($actor, 'sanctum')
            ->deleteJson('/api/v1/users/'.$target->id)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Cannot delete user with rental history');
    }
}
