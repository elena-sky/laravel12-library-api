<?php

namespace Tests\Unit\Actions\User;

use App\Actions\User\UpdateUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_only_name_when_email_omitted(): void
    {
        $user = User::factory()->create(['name' => 'Old', 'email' => 'keep@example.com']);

        $updated = (new UpdateUserAction)->execute($user, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
        $this->assertSame('keep@example.com', $updated->email);
    }

    public function test_updates_only_email_when_name_omitted(): void
    {
        $user = User::factory()->create(['name' => 'KeepName', 'email' => 'old@example.com']);

        $updated = (new UpdateUserAction)->execute($user, ['email' => 'fresh@example.com']);

        $this->assertSame('KeepName', $updated->name);
        $this->assertSame('fresh@example.com', $updated->email);
    }

    public function test_empty_payload_does_not_touch_database(): void
    {
        $user = User::factory()->create(['name' => 'A', 'email' => 'a@example.com']);
        $before = $user->updated_at?->toIso8601String();

        $updated = (new UpdateUserAction)->execute($user, []);

        $this->assertSame('A', $updated->name);
        $this->assertSame('a@example.com', $updated->email);
        $user->refresh();
        $this->assertSame($before, $user->updated_at?->toIso8601String());
    }
}
