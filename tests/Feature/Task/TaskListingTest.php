<?php

namespace Task;

use App\Enums\TaskStatus;
use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class TaskListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_a_board_tasks_among_other_tasks()
    {
        $user = User::factory()->create();

        // Create 2 tasks for other users
        // Create 2 tasks for same user but different boards
        // Create 2 tasks for a specific board

        Task::factory()->count(2)->create();

        Task::factory()->recycle($user)->count(2)->create();

        $board = Board::factory()->recycle($user)->create();

        $tasks = Task::factory()->recycle($board)->recycle($user)->count(2)->sequence([
            [
                'name' => 'Sample 1',
                'description' => 'Description 1',
                'deadline' => '2021-01-01 00:00:00',
                'status' => TaskStatus::TODO
            ],
            [
                'name' => 'Sample 2',
                'description' => 'Description 2',
                'deadline' => '2022-02-02 00:00:00',
                'status' => TaskStatus::COMPLETED
            ],
        ])->create();

        $this
            ->actingAs($user)
            ->getJson("/api/tasks?board_id={$board->id}")
            ->assertOk()
            ->dump()
            ->assertJson([
                [
                    'id' => $tasks[0]->id,
                    'name' => 'Sample 1',
                    'description' => 'Description 1',
                    'deadline' => '2021-01-01 00:00:00',
                    'status' => TaskStatus::TODO
                ],
                [
                    'id' => $tasks[1]->id,
                    'name' => 'Sample 2',
                    'description' => 'Description 2',
                    'deadline' => '2022-02-02 00:00:00',
                    'status' => TaskStatus::COMPLETED
                ]
            ]);
    }

    public function test_guest_can_not_list_any_task()
    {
        $task = Task::factory()->create();

        $this
            ->getJson("/api/tasks?board_id={$task->board->id}")
            ->assertUnauthorized();
    }
}
