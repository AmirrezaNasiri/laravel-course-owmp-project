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

class ProjectCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_project()
    {
        # Preparation
        $user = User::factory()->create();

        $this->actingAs($user);

        # Action
        $response = $this->postJson('/api/projects', [
            'name' => 'Sample 1',
        ]);

        # Assertion
        $response->assertOk();

        self::assertEquals('Sample 1', $user->projects()->first()->name);
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
    public function test_user_can_not_create_project_with_empty_name($invalidName)
    {
        # Preparation
        $user = User::factory()->create();

        $this->actingAs($user);

        # Action
        $response = $this->postJson('/api/projects', [
            'name' => $invalidName,
        ]);

        # Assertion
        $response->assertUnprocessable()->assertJsonValidationErrorFor('name');
        self::assertEmpty(Project::count());
    }

    public function test_user_can_not_create_a_project_if_the_name_already_exists()
    {
        # Preparation
        $user = User::factory()->create();

        $user->projects()->create([
            'name' => 'Sample'
        ]);

        $this->actingAs($user);

        # Action
        $response = $this->postJson('/api/projects', [
            'name' => 'Sample',
        ]);

        # Assertion
        $response->assertUnprocessable()->assertJsonValidationErrorFor('name');
        self::assertEquals(1, $user->projects()->count());
    }

    public function test_user_can_create_a_project_even_if_another_user_has_a_project_with_the_same_name()
    {
        # Preparation
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $userB->projects()->create([
            'name' => 'Sample'
        ]);

        $this->actingAs($userA);

        # Action
        $response = $this->postJson('/api/projects', [
            'name' => 'Sample',
        ]);

        # Assertion
        $response->assertOk();
        self::assertEquals(1, $userA->projects()->count());
    }

    public function test_guest_can_not_create_a_project()
    {
        # Action
        $response = $this->postJson('/api/projects', [
            'name' => 'Sample 1',
        ]);

        # Assertion
        $response->assertUnauthorized();
        self::assertEmpty(Project::count());
    }
}
