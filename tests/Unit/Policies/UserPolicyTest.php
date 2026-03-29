<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_actor_may_view_any(): void
    {
        $alice = User::factory()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->viewAny($alice));
    }

    public function test_authenticated_actor_may_view_update_delete_other_user(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->view($alice, $bob));
        $this->assertTrue($policy->update($alice, $bob));
        $this->assertTrue($policy->delete($alice, $bob));
    }

    public function test_authenticated_actor_may_create(): void
    {
        $alice = User::factory()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->create($alice));
    }
}
