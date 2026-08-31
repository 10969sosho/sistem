<?php

namespace Database\Factories;

use App\Models\Debt;
use Illuminate\Database\Eloquent\Factories\Factory;

class DebtFactory extends Factory
{
    protected $model = Debt::class;

    public function definition(): array
    {
        return [
            'date' => $this->faker->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'type' => $this->faker->randomElement(['talangan', 'pinjaman', 'reimburse']),
            'person' => $this->faker->randomElement(['CECIL', 'TIAN']),
            'description' => $this->faker->sentence(3),
            'amount' => $this->faker->numberBetween(50000, 1000000),
            'status' => $this->faker->randomElement(['belum_dibayar', 'dibayar_sebagian', 'lunas']),
            'paid_date' => $this->faker->optional(0.3)->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
