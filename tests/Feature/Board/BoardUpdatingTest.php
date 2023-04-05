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

class BoardUpdatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_a_board()
    {
        $board = Board::factory()->recycle(User::factory()->create())->create([
            'name' => 'Old Name'
        ]);

        $this
            ->actingAs($board->creator)
            ->putJson("/api/boards/{$board->id}", [
                'name' => 'New Name',
            ])
            ->assertOk()
            ->assertJson([
                'id' => $board->id,
                'name' => 'New Name'
            ]);

        self::assertEquals('New Name', $board->fresh()->name);
    }

    public function test_user_can_update_board_even_if_the_name_does_not_change()
    {
        $board = Board::factory()->recycle(User::factory()->create())->create([
            'name' => 'Board Name'
        ]);

        $this
            ->actingAs($board->creator)
            ->putJson("/api/boards/{$board->id}", [
                'name' => 'Board Name',
            ])
            ->assertOk()
            ->assertJson([
                'id' => $board->id,
                'name' => 'Board Name'
            ]);

        self::assertEquals('Board Name', $board->fresh()->name);
    }

    public function invalidNameProvider()
    {
        return [
            'empty' => [''],
            'non-string' => [ ['string'] ],
            'too small' => ['abc'],
            'too long' => [str_repeat('a', 1000)],
        ];
    }

    /**
     * @dataProvider invalidNameProvider
     * @return void
     */
    public function test_user_can_not_update_a_board_with_invalid_name($invalidName)
    {
        $board = Board::factory()->recycle(User::factory()->create())->create([
            'name' => 'Old Name'
        ]);

        $this
            ->actingAs($board->creator)
            ->putJson("/api/boards/{$board->id}", [
                'name' => $invalidName,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('name');

        self::assertEquals('Old Name', $board->fresh()->name);
    }

    public function test_user_can_not_update_a_board_to_duplicated_name_on_the_same_project()
    {
        $project = Project::factory()->create();

        $boards = Board::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Board Alpha'],
                ['name' => 'Board Beta'],
            )
            ->recycle($project)
            ->recycle($project->creator)
            ->create();

        $this
            ->actingAs($project->creator)
            ->putJson("/api/boards/{$boards[1]->id}", [
                'name' => 'Board Alpha',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('name');

        self::assertEquals('Board Beta', $boards[1]->fresh()->name);
    }

    public function test_user_can_update_a_board_to_duplicated_name_on_different_projects()
    {
        $creator = User::factory()->create();

        $boards = Board::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Board Alpha'],
                ['name' => 'Board Beta'],
            )
            ->recycle($creator)
            ->create();

        $this
            ->actingAs($creator)
            ->putJson("/api/boards/{$boards[1]->id}", [
                'name' => 'Board Alpha',
            ])
            ->assertOk();

        self::assertEquals('Board Alpha', $boards[1]->fresh()->name);
    }

    public function test_user_can_not_update_another_users_board()
    {
        $user = User::factory()->create();

        $board = Board::factory()->create([
            'name' => 'Old Name'
        ]);

        $this
            ->actingAs($user)
            ->putJson("/api/boards/{$board->id}", [
                'name' => 'New Name',
            ])
            ->assertNotFound();

        self::assertEquals('Old Name', $board->fresh()->name);
    }

    public function test_guest_can_not_update_any_project()
    {
        $board = Board::factory()->create([
            'name' => 'Old Name'
        ]);

        $this
            ->putJson("/api/boards/{$board->id}", [
                'name' => 'New Name',
            ])
            ->assertUnauthorized();

        self::assertEquals('Old Name', $board->fresh()->name);
    }
}
