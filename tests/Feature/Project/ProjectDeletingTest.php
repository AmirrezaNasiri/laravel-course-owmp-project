<?php

namespace Project;

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
use function PHPUnit\Framework\assertEquals;

class ProjectDeletingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_a_project()
    {
        # Preparation
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'creator')->create();

        Task::factory()->recycle($project)->recycle($user)->create();

        $this->actingAs($user);

        # Action
        $response = $this->deleteJson("/api/projects/{$project->id}");

        # Assertion
        $response->assertOk();

        self::assertEmpty(Project::count());
        self::assertEmpty(Board::count());
        self::assertEmpty(Task::count());
    }

    public function test_user_can_not_delete_others_projects()
    {
        # Preparation
        $userA = User::factory()->create();

        $project = Project::factory()->create();

        $this->actingAs($userA);

        # Action
        $response = $this->deleteJson("/api/projects/{$project->id}");

        # Assertion
        $response->assertNotFound();

        self::assertEquals(1, Project::count());
    }

    public function test_guess_can_not_delete_any_project()
    {
        # Preparation
        $project = Project::factory()->create();

        # Action
        $response = $this->deleteJson("/api/projects/{$project->id}");

        # Assertion
        $response->assertUnauthorized();

        self::assertEquals(1, Project::count());
    }
}
