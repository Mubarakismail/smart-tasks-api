<?php

namespace Modules\Task\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Task\Models\Status;

class StatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Status::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'code' => $this->faker->slug,
            'order' => rand(1, 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

