<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_may_view_only_self(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->view($alice, $alice));
        $this->assertFalse($policy->view($alice, $bob));
    }

    public function test_user_may_update_only_self(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->update($alice, $alice));
        $this->assertFalse($policy->update($alice, $bob));
    }
}
