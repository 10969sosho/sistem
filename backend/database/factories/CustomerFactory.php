<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'pic_name' => fake()->name(),
            'whatsapp' => fake()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function nonActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'non_active',
        ]);
    }
}
