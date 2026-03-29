<?php

namespace Tests\Unit\Actions\User;

use App\Actions\User\DeleteUserAction;
use App\Exceptions\ResourceConflictException;
use App\Models\BookRent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_other_user_when_no_rentals(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        (new DeleteUserAction)->execute($actor, $target);

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_throws_when_actor_deletes_self(): void
    {
        $user = User::factory()->create();

        $this->expectException(ResourceConflictException::class);
        $this->expectExceptionMessage('Cannot delete your own account');

        (new DeleteUserAction)->execute($user, $user);
    }

    public function test_throws_when_target_has_rentals(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        BookRent::factory()->create(['user_id' => $target->id]);

        $this->expectException(ResourceConflictException::class);
        $this->expectExceptionMessage('Cannot delete user with rental history');

        (new DeleteUserAction)->execute($actor, $target);
    }
}
