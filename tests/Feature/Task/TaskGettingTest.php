<?php

namespace Task;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\TaskStatus;
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

class TaskGettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_a_task()
    {
        $task = Task::factory()->isTodo()->create([
            'name' => 'Task Name',
            'description' => 'Sample description of task.',
            'deadline' => '2030-01-01 00:00:00',
        ]);

        $this
            ->actingAs($task->creator)
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJson([
                'id' => $task->id,
                'name' => 'Task Name',
                'description' => 'Sample description of task.',
                'deadline' => '2030-01-01T00:00:00.000000Z',
                'status' => TaskStatus::TODO->value,
                'board_id' => $task->board->id
            ]);
    }

    public function test_user_can_not_get_another_users_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $this
            ->actingAs($user)
            ->getJson("/api/tasks/{$task->id}")
            ->assertNotFound();
    }

    public function test_guest_can_not_get_any_task()
    {
        $task = Task::factory()->create();

        $this
            ->getJson("/api/tasks/{$task->id}")
            ->assertUnauthorized();
    }
}
