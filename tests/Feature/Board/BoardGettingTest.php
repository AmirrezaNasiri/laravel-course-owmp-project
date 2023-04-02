<?php

namespace Board;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class BoardGettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_a_board()
    {
        $board = Board::factory()->create([
            'name' => 'Board Name'
        ]);

        $this
            ->actingAs($board->creator)
            ->getJson("/api/boards/{$board->id}")
            ->assertOk()
            ->assertJson([
                'id' => $board->id,
                'name' => 'Board Name'
            ]);
    }

    public function test_user_can_not_get_another_users_board()
    {
        $user = User::factory()->create();
        $board = Board::factory()->create([
            'name' => 'Board Name'
        ]);

        $this
            ->actingAs($user)
            ->getJson("/api/boards/{$board->id}")
            ->assertNotFound();
    }

    public function test_guest_can_not_get_any_board()
    {
        $board = Board::factory()->create();

        $this
            ->getJson("/api/boards/{$board->id}")
            ->assertUnauthorized();
    }
}
