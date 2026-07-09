<?php

namespace Database\Factories;

use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $authorId = User::query()->inRandomOrder()->value('id')
            ?? User::factory()->create()->id;

        $assigneeId = User::query()->inRandomOrder()->value('id')
            ?? $authorId;

        return [
            'title' => fake()->sentence(5),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(array_column(TaskStatusEnum::cases(), 'value')),
            'user_id' => $authorId,
            'assigned_to' => $assigneeId,
            'due_date' => fake()->optional()->dateTimeBetween('now', '+60 days'),
        ];
    }
}
