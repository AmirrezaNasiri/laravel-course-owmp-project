<?php

namespace Board;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BoardDeletingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_a_board()
    {
        $board = Board::factory()->create();

        Task::factory()->recycle($board->creator)->recycle($board)->create();

        $this
            ->actingAs($board->creator)
            ->deleteJson("/api/boards/{$board->id}")
            ->assertOk();

        self::assertEmpty(Board::count());
        self::assertEmpty(Task::count());
    }

    public function test_user_can_not_delete_others_boards()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create();

        $this
            ->actingAs($user)
            ->deleteJson("/api/boards/{$board->id}")
            ->assertNotFound();

        self::assertEquals(1, Board::count());
    }

    public function test_guess_can_not_delete_any_board()
    {
        $board = Board::factory()->create();

        $this
            ->deleteJson("/api/boards/{$board->id}")
            ->assertUnauthorized();

        self::assertEquals(1, Board::count());
    }
}
