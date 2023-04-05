<?php

namespace Task;

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

class TaskDeletingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_a_task()
    {
        $task = Task::factory()->create();

        $this->withoutExceptionHandling();
        $this
            ->actingAs($task->creator)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertOk();

        self::assertEmpty(Task::count());
    }

    public function test_user_can_not_delete_others_tasks()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $this
            ->actingAs($user)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertNotFound();

        self::assertEquals(1, Task::count());
    }

    public function test_guess_can_not_delete_any_board()
    {
        $task = Task::factory()->create();

        $this
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertUnauthorized();

        self::assertEquals(1, Task::count());
    }
}
