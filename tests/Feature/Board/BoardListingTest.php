<?php

namespace Board;

use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class BoardListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_a_project_boards_among_other_boards()
    {
        $user = User::factory()->create();

        $this->withoutExceptionHandling();
        // Create 2 boards for other users
        // Create 2 boards for same user but different projects
        // Create 2 boards for a specific project

        Board::factory()->count(2)->create();

        Board::factory()->recycle($user)->count(2)->create();

        $project = Project::factory()->recycle($user)->create();

        $boards = Board::factory()->recycle($project)->recycle($user)->count(2)->sequence(
            [ 'name' => 'Sample 1' ],
            [ 'name' => 'Sample 2' ]
        )->create();

        $this
            ->actingAs($user)
            ->getJson("/api/boards?project_id={$project->id}")
            ->assertOk()
            ->dump()
            ->assertJson([
                [
                    'id' => $boards[0]->id,
                    'name' => 'Sample 1'
                ],
                [
                    'id' => $boards[1]->id,
                    'name' => 'Sample 2'
                ]
            ]);
    }

    public function test_guest_can_not_list_any_project()
    {
        $board = Board::factory()->create();

        $this
            ->getJson("/api/boards?project_id={$board->project->id}")
            ->assertUnauthorized();
    }
}
