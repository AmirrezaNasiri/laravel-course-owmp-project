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

class ProjectUpdatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_project()
    {
        # Preparation
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'Old Name',
        ]);

        $this->actingAs($user);

        # Action
        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => 'New Name',
        ]);

        # Assertion
        $response->assertOk();

        $project->refresh();

        self::assertEquals('New Name', $project->name);
    }

    public function test_user_can_update_project_even_if_the_name_does_not_change()
    {
        # Preparation
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'Alpha',
        ]);

        $this->actingAs($user);

        # Action
        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => 'Alpha',
        ]);

        # Assertion
        $response->assertOk();

        $project->refresh();

        self::assertEquals('Alpha', $project->name);
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
    public function test_user_can_not_update_a_project_with_invalid_name($invalidName)
    {
        # Preparation
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'Old Name',
        ]);

        $this->actingAs($user);

        # Action
        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => $invalidName,
        ]);

        # Assertion
        $response->assertUnprocessable()->assertJsonValidationErrorFor('name');

        $project->refresh();

        self::assertEquals('Old Name', $project->name);
    }

    public function test_user_can_not_update_a_project_with_same_name()
    {
        $user = User::factory()->create();
        $projectA = $user->projects()->create([
            'name' => 'Alpha',
        ]);
        $projectB = $user->projects()->create([
            'name' => 'Beta',
        ]);

        $this->actingAs($user);

        # Action
        $response = $this->putJson("/api/projects/{$projectB->id}", [
            'name' => 'Alpha',
        ]);

        # Assertion
        $response->assertUnprocessable()->assertJsonValidationErrorFor('name');

        $projectB->refresh();

        self::assertEquals('Beta', $projectB->name);
    }

    public function test_user_can_update_a_project_even_if_another_user_has_a_project_with_same_name()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $projectA = $userA->projects()->create([
            'name' => 'Alpha',
        ]);
        $projectB = $userB->projects()->create([
            'name' => 'Beta',
        ]);

        $this->actingAs($userA);

        # Action
        $response = $this->putJson("/api/projects/{$projectA->id}", [
            'name' => 'Beta',
        ]);

        # Assertion
        $response->assertOk();

        $projectA->refresh();

        self::assertEquals('Beta', $projectA->name);
    }

    public function test_user_can_not_update_another_users_project()
    {
        # Preparation
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $project = $userB->projects()->create([
            'name' => 'Old Name',
        ]);

        $this->actingAs($userA);

        # Action
        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => 'New Name',
        ]);

        # Assertion
        $response->assertNotFound();

        $project->refresh();

        self::assertEquals('Old Name', $project->name);
    }

    public function test_guest_can_not_update_any_project()
    {
        # Preparation
        $user = User::factory()->create();
        $project = $user->projects()->create([
            'name' => 'Old Name',
        ]);

        # Action
        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => 'New Name',
        ]);

        # Assertion
        $response->assertUnauthorized();

        $project->refresh();

        self::assertEquals('Old Name', $project->name);
    }
}
