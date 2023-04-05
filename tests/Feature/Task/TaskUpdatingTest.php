<?php

namespace Task;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class TaskUpdatingTest extends TestCase
{
    use RefreshDatabase;

    public static function taskFieldPatchProvider()
    {
        return [
            'a good description' => ['description', 'My new description.'],
            'empty description' => ['description', ''],
            'a new name' => ['name', 'My New Task'],
            'a new deadline' => ['deadline', '2050-01-01 01:01:01', '2050-01-01T01:01:01.000000Z'],
            'empty deadline' => ['deadline', ''],
        ];
    }

    /**
     * @dataProvider taskFieldPatchProvider
     */
    public function test_user_can_update_a_task($field, $value, $expectedValue = null)
    {
        $oldValues = [
            'name' => 'My Old Task',
            'description' => 'My old description.',
            'deadline' => '1999-01-01 00:00:00'
        ];

        $task = Task::factory()->create($oldValues);

        $expectedAttributes = [
            'name' => 'My Old Task',
            'description' => 'My old description.',
            'deadline' => '1999-01-01T00:00:00.000000Z',
            $field => $expectedValue ?: $value
        ];

        $this
            ->actingAs($task->creator)
            ->patchJson("/api/tasks/{$task->id}", [
                $field => $value,
            ])
            ->assertOk()
            ->assertJson([
                'id' => $task->id,
                ...$expectedAttributes
            ]);

        self::assertEquals(
            $expectedAttributes,
            Arr::only($task->fresh()->toArray(), array_keys($expectedAttributes))
        );
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

    /**
     * @dataProvider \Tests\Assets\InvalidValueProvider::emptyStringProvider
     * @dataProvider \Tests\Assets\InvalidValueProvider::invalidNameProvider
     */
    public function test_user_can_not_update_task_with_invalid_name($invalidName)
    {
        $task = Board::factory()->create([
            'name' => 'Old Name'
        ]);

        $this
            ->actingAs($task->creator)
            ->patchJson("/api/tasks/{$task->id}", [
                'name' => $invalidName
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('name');

        self::assertEquals('Old Name', $task->fresh()->name);
    }

    /**
     * @dataProvider \Tests\Assets\InvalidValueProvider::invalidDescriptionProvider
     */
    public function test_user_can_not_update_task_with_invalid_description($invalidDescription)
    {
        $task = Task::factory()->create([
            'description' => 'Old Description'
        ]);

        $this
            ->actingAs($task->creator)
            ->patchJson("/api/tasks/{$task->id}", [
                'description' => $invalidDescription
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('description');

        self::assertEquals('Old Description', $task->fresh()->description);
    }

    /**
     * @dataProvider \Tests\Assets\InvalidValueProvider::invalidDatetimeProvider
     */
    public function test_user_can_not_update_task_with_invalid_deadline($invalidDeadline)
    {
        $task = Task::factory()->create([
            'deadline' => '2022-01-01 00:00:00'
        ]);

        $this
            ->actingAs($task->creator)
            ->patchJson("/api/tasks/{$task->id}", [
                'deadline' => $invalidDeadline
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('deadline');

        self::assertEquals('2022-01-01 00:00:00', $task->fresh()->deadline->toDateTimeString());
    }

    public function test_user_can_not_update_another_users_task()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'name' => 'Old Name'
        ]);

        $this
            ->actingAs($user)
            ->patchJson("/api/tasks/{$task->id}", [
                'name' => 'New Name',
            ])
            ->assertNotFound();

        self::assertEquals('Old Name', $task->fresh()->name);
    }

    public function test_guest_can_not_update_any_task()
    {
        $task = Task::factory()->create([
            'name' => 'Old Name'
        ]);

        $this
            ->patchJson("/api/tasks/{$task->id}", [
                'name' => 'New Name'
            ])
            ->assertUnauthorized();

        self::assertEquals('Old Name', $task->fresh()->name);
    }
}
