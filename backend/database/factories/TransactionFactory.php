<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'type' => $this->faker->randomElement(['pemasukan', 'pengeluaran']),
            'category' => $this->faker->randomElement([
                'Pendapatan Penjualan',
                'Pendapatan Project',
                'Pendapatan Maintenance',
                'Pendapatan Lainnya',
                'Beban Gaji',
                'Beban Marketing',
                'Beban AI',
                'Beban Software',
                'Beban Transportasi',
                'Beban Operasional Kantor',
            ]),
            'description' => $this->faker->sentence(3),
            'vendor' => $this->faker->optional(0.6)->company(),
            'amount' => $this->faker->numberBetween(10000, 5000000),
            'status' => $this->faker->randomElement(['paid', 'pending', 'cancelled']),
            'payment_method' => $this->faker->randomElement(['Transfer', 'Cash', 'QRIS']),
        ];
    }
}
