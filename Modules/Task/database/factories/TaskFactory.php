<?php

namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Task\Models\Status;
use Modules\Task\Models\Task;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status_id' => fn() => Status::inRandomOrder()->value('id') ?? 1,
            'creator_id' => fn() => User::factory()->create()->id,
            'assignee_id' => fn() => User::factory()->create()->id,
            'due_date' => now()->addDays(rand(1, 30)),
            'priority' => rand(1, 5),
        ];
    }
}

