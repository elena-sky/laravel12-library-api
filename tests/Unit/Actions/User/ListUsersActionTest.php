<?php

namespace Tests\Unit\Actions\User;

use App\Actions\User\ListUsersAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListUsersActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginates_ordered_by_id(): void
    {
        User::factory()->count(4)->create();

        $paginator = (new ListUsersAction)->execute(2);

        $this->assertSame(2, $paginator->perPage());
        $this->assertGreaterThanOrEqual(4, $paginator->total());
        $this->assertCount(2, $paginator->items());

        $ids = array_map(static fn (User $u): int => $u->id, $paginator->items());
        $sorted = $ids;
        sort($sorted);
        $this->assertSame($sorted, $ids, 'Page items should be ordered by id ascending');
    }
}
