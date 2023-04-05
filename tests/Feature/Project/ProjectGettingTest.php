<?php

namespace Project;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class ProjectGettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_a_project()
    {
        $user = User::factory()->create();

        $project = Project::factory()->recycle($user)->create([
            'name' => 'Sample 1'
        ]);

        $this
            ->actingAs($user)
            ->getJson("/api/projects/{$project->id}")
            ->assertOk()
            ->assertJson([
                'id' => $project->id,
                'name' => 'Sample 1'
            ]);
    }

    public function test_user_can_not_get_another_users_project()
    {
        $user = User::factory()->create();

        $project = Project::factory()->create();

        $this
            ->actingAs($user)
            ->getJson("/api/projects/{$project->id}")
            ->assertNotFound();
    }

    public function test_guest_can_not_get_any_project()
    {
        $project = Project::factory()->create();

        $this
            ->getJson("/api/projects/{$project->id}")
            ->assertUnauthorized();
    }
}
