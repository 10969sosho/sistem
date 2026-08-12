<?php

namespace Database\Factories;

use App\Models\Finance;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Finance>
 */
class FinanceFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->numberBetween(3_000_000, 50_000_000);

        return [
            'project_id' => Project::factory(),
            'total' => $total,
            'dp' => round($total * 0.3, -3),
            'termin1' => 0,
            'termin2' => 0,
            'termin3' => 0,
            'pelunasan' => 0,
        ];
    }

    public function paidInFull(): static
    {
        return $this->state(fn (array $attributes) => [
            'dp' => 0,
            'termin1' => 0,
            'termin2' => 0,
            'termin3' => 0,
            'pelunasan' => $attributes['total'] ?? 1_000_000,
        ]);
    }
}
