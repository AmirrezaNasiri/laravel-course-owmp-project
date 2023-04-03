<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'creator_id' => User::factory(),
            'task_id' => Task::factory(),
            'name' => fake()->name(),
            'description' => fake()->optional()->text(),
            'deadline' => fake()->optional()->dateTime(),
            'status' => fake()->randomElement(TaskStatus::cases()),
        ];
    }

    /**
     * @return Factory
     */
    public function isTodo(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::TODO,
        ]);
    }
}
