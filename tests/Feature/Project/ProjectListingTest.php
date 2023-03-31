<?php

namespace Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class ProjectListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_projects_among_all_projects()
    {
        $user = User::factory()->create();

        Project::factory()->count(3)->create();

        $projects = Project::factory()->count(2)->sequence([
            [ 'name' => 'Sample 1' ],
            [ 'name' => 'Sample 2' ]
        ])->forCreator($user)->create();

        $this
            ->actingAs($user)
            ->getJson("/api/projects")
            ->assertOk()
            ->assertJson([
                [
                    'id' => $projects[0]->id,
                    'name' => 'Sample 1'
                ],
                [
                    'id' => $projects[1]->id,
                    'name' => 'Sample 2'
                ]
            ]);
    }

    public function test_guest_can_not_list_any_project()
    {
        Project::factory()->count(3)->create();

        $this
            ->getJson("/api/projects")
            ->assertUnauthorized();
    }
}
