<?php

namespace Tests\Unit\Policies;

use App\Models\Book;
use App\Models\User;
use App\Policies\BookPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_may_list_and_mutate_catalog(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $book = Book::factory()->create();
        $policy = new BookPolicy;

        $this->assertTrue($policy->viewAny($alice));
        $this->assertTrue($policy->view($alice, $book));
        $this->assertTrue($policy->create($alice));
        $this->assertTrue($policy->update($alice, $book));
        $this->assertTrue($policy->delete($alice, $book));

        $this->assertTrue($policy->viewAny($bob));
        $this->assertTrue($policy->view($bob, $book));
        $this->assertTrue($policy->create($bob));
        $this->assertTrue($policy->update($bob, $book));
        $this->assertTrue($policy->delete($bob, $book));
    }
}
