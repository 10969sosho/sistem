<?php

namespace Database\Factories;

use App\Models\Hosting;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hosting>
 */
class HostingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'provider' => fake()->randomElement(['Niagahoster', 'IDCloudHost', 'Hostinger', 'Vultr', 'DigitalOcean']),
            'package' => fake()->randomElement(['Business', 'Premium', 'Deluxe', 'VPS 2GB', 'VPS 4GB']),
            'expired_date' => fake()->dateTimeBetween('-2 months', '+2 months')->format('Y-m-d'),
            'domain' => fake()->domainName(),
            'registrar' => fake()->randomElement(['Niagahoster', 'Namecheap', 'Cloudflare', 'GoDaddy']),
            'domain_expired_date' => fake()->dateTimeBetween('-2 months', '+6 months')->format('Y-m-d'),
            'ssl_status' => fake()->randomElement(['active', 'non_active']),
            'ssl_expired_date' => fake()->dateTimeBetween('-1 month', '+6 months')->format('Y-m-d'),
            'server_ip' => fake()->ipv4(),
            'panel' => 'cPanel',
            'username' => fake()->userName(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
