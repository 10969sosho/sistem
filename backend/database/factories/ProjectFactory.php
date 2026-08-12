<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->catchPhrase(),
            'type' => fake()->randomElement(['Website', 'Mobile App', 'Web App', 'E-Commerce', 'UI/UX', 'Maintenance']),
            'description' => fake()->paragraph(),
            'status' => 'progress',
            'deadline' => fake()->dateTimeBetween('-1 month', '+2 months')->format('Y-m-d'),
            'pic' => fake()->name(),
            'start_date' => fake()->dateTimeBetween('-3 months', '-1 day')->format('Y-m-d'),
            'finish_date' => null,
            'internal_notes' => fake()->optional()->sentence(),
        ];
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
