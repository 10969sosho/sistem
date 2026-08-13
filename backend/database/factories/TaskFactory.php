<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'customer_id' => Customer::factory(),
            'project_id' => null,
            'type' => fake()->randomElement(['development', 'revisi', 'bug_fix', 'maintenance']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => 'todo',
            'cabang' => fake()->optional(0.5)->randomElement(['tian', 'cecil']),
            'pic' => fake()->name(),
            'deadline' => fake()->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d'),
            'estimate' => fake()->optional()->randomElement(['2 jam', '4 jam', '1 hari', '2 hari', '1 minggu']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    public function forProject(?Project $project = null): static
    {
        return $this->state(function (array $attributes) use ($project) {
            $project ??= Project::factory()->create();

            return [
                'project_id' => $project->id,
                'customer_id' => $project->customer_id,
            ];
        });
    }
}
