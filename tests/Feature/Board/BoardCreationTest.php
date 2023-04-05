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

class BoardCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_board()
    {
        $project = Project::factory()->create();

        $this->actingAs($project->creator)
            ->postJson("/api/boards", [
                'project_id' => $project->id,
                'name' => 'My Board'
            ])
            ->assertOk()
            ->assertJson([
                'id' => ($board = Board::first())->id,
                'name' => 'My Board'
            ]);

        self::assertTrue($board->project()->is($project));
        self::assertTrue($board->creator()->is($project->creator));
        self::assertEquals('My Board', $board->name);
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
    public function test_user_can_not_create_board_with_invalid_name($invalidName)
    {
        $project = Project::factory()->create();

        $this->actingAs($project->creator)
            ->postJson("/api/boards", [
                'project_id' => $project->id,
                'name' => $invalidName
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('name');

        $this->assertDatabaseEmpty(Board::class);
    }

    public function test_user_can_not_create_board_with_a_project_it_does_not_have_access()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)
            ->postJson("/api/boards", [
                'project_id' => $project->id,
                'name' => 'My Board'
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('project_id');

        self::assertFalse(Board::exists());
    }

    public function test_user_can_not_create_a_board_if_the_name_already_exists_in_the_project()
    {
        $project = Project::factory()->create();
        Board::factory()->recycle($project->creator)->recycle($project)->create([
            'name' => 'My Board'
        ]);

        $this->actingAs($project->creator)
            ->postJson("/api/boards", [
                'project_id' => $project->id,
                'name' => 'My Board'
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('name');

        self::assertEquals(1, Board::count());
    }

    public function test_user_can_create_a_board_even_if_another_project_has_a_board_with_the_same_name()
    {
        $user = User::factory()->create();

        $projects = Project::factory()->count(2)->recycle($user)->create();

        Board::factory()->for($projects[0])->create([
            'name' => 'My Board'
        ]);

        $this->actingAs($user)
            ->postJson("/api/boards", [
                'project_id' => $projects[1]->id,
                'name' => 'My Board'
            ])
            ->assertOk();

        self::assertEquals(2, Board::count());
    }

    public function test_guest_can_not_create_a_project()
    {
        $project = Project::factory()->create();

        $this
            ->postJson('/api/boards', [
                'project_id' => $project->id,
                'name' => 'My Board',
            ])
            ->assertUnauthorized();

        self::assertEmpty(Board::count());
    }
}
