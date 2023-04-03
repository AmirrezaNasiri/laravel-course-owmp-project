<?php

namespace Task;

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

class TaskCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_task()
    {
        $board = Board::factory()->create();

        $this->actingAs($board->creator)
            ->postJson("/api/tasks", [
                'board_id' => $board->id,
                'name' => 'My Task',
                'description' => 'This is a sample task.',
                'deadline' => '2025-01-01 00:00:00'
            ])
            ->assertOk()
            ->assertJson([
                'id' => ($task = Task::first())->id,
                'board_id' => $board->id,
                'name' => 'My Task',
                'description' => 'This is a sample task.',
                'deadline' => '2025-01-01 00:00:00'
            ]);

        self::assertTrue($task->board()->is($board));
        self::assertTrue($task->creator()->is($board->creator));
        self::assertEquals('My Task', $task->name);
        self::assertEquals('This is a sample task.', $task->description);
        self::assertEquals('2025-01-01 00:00:00', $task->deadline->toDateTimeString());
    }

    /**
     * @dataProvider \Tests\Assets\InvalidValueProvider::emptyStringProvider
     * @dataProvider \Tests\Assets\InvalidValueProvider::invalidNameProvider
     */
    public function test_user_can_not_create_task_with_invalid_name($invalidName)
    {
        $board = Board::factory()->create();

        $this
            ->actingAs($board->creator)
            ->postValidJson(
                board: $board,
                name: $invalidName
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('name');

        self::assertFalse(Task::exists());
    }

    /**
     * @dataProvider \Tests\Assets\InvalidValueProvider::invalidDescriptionProvider
     */
    public function test_user_can_not_create_task_with_invalid_description($invalidDescription)
    {
        $board = Board::factory()->create();

        $this
            ->actingAs($board->creator)
            ->postValidJson(
                board: $board,
                description: $invalidDescription
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('name');

        self::assertFalse(Task::exists());
    }

    /**
     * @dataProvider \Tests\Assets\InvalidValueProvider::invalidDatetimeProvider
     */
    public function test_user_can_not_create_task_with_invalid_deadline($invalidDatetime)
    {
        $board = Board::factory()->create();

        $this
            ->actingAs($board->creator)
            ->postValidJson(
                board: $board,
                deadline: $invalidDatetime
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('deadline');

        self::assertFalse(Task::exists());
    }

    public function test_user_can_not_create_task_with_a_board_it_does_not_have_access()
    {
        $board = Board::factory()->create();

        $this
            ->actingAs($board->creator)
            ->postValidJson(
                board: $board,
            )
            ->assertNotFound();

        self::assertFalse(Task::exists());
    }

    public function test_guest_can_not_create_a_task()
    {
        $board = Board::factory()->create();

        $this
            ->postValidJson(
                board: $board,
            )
            ->assertUnauthorized();

        self::assertFalse(Task::exists());
    }

    private function postValidJson(
        Board $board,
        string $name = 'My Task',
        string $description = 'This is a sample task.',
        string $deadline = '2025-01-01 00:00:00',
    )
    {
        return $this->postJson("/api/tasks", [
            'board_id' => $board->id,
            'name' => $name,
            'description' => $description,
            'deadline' => $deadline
        ]);
    }
}
