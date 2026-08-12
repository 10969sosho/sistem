<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Finance;
use App\Models\Hosting;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_returns_widget_data(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'date',
                    'today_tasks' => ['count', 'items'],
                    'overdue_tasks' => ['count', 'items'],
                    'week_tasks' => ['count', 'items'],
                    'active_projects' => ['count', 'items'],
                    'revisi_projects' => ['count', 'items'],
                    'hosting_expiring' => ['count', 'items'],
                    'domain_expiring' => ['count', 'items'],
                    'unpaid_invoices' => ['count', 'items'],
                ],
            ]);
    }

    public function test_dashboard_counts_today_tasks(): void
    {
        $customer = Customer::factory()->create();
        Task::factory(3)->create([
            'customer_id' => $customer->id,
            'deadline' => now()->toDateString(),
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'today_tasks' => [
                        'count' => 3,
                    ],
                ],
            ]);
    }

    public function test_dashboard_counts_overdue_tasks(): void
    {
        $customer = Customer::factory()->create();
        Task::factory(2)->create([
            'customer_id' => $customer->id,
            'deadline' => now()->subDays(2)->toDateString(),
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'overdue_tasks' => [
                        'count' => 2,
                    ],
                ],
            ]);
    }

    public function test_dashboard_counts_revisi_projects(): void
    {
        $customer = Customer::factory()->create();
        Project::factory(2)->create([
            'customer_id' => $customer->id,
            'status' => 'revisi',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'revisi_projects' => [
                        'count' => 2,
                    ],
                ],
            ]);
    }

    public function test_dashboard_counts_expiring_hosting(): void
    {
        $customer = Customer::factory()->create();
        $project1 = Project::factory()->create(['customer_id' => $customer->id]);
        $project2 = Project::factory()->create(['customer_id' => $customer->id]);
        Hosting::factory()->create([
            'project_id' => $project1->id,
            'expired_date' => now()->addDays(10)->toDateString(),
        ]);
        Hosting::factory()->create([
            'project_id' => $project2->id,
            'expired_date' => now()->addDays(10)->toDateString(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'hosting_expiring' => [
                        'count' => 2,
                    ],
                ],
            ]);
    }

    public function test_dashboard_counts_unpaid_invoices(): void
    {
        $customer = Customer::factory()->create();
        $project1 = Project::factory()->create(['customer_id' => $customer->id]);
        $project2 = Project::factory()->create(['customer_id' => $customer->id]);
        Finance::factory()->create([
            'project_id' => $project1->id,
            'total' => 10000000,
            'dp' => 3000000,
            'termin1' => 0,
            'termin2' => 0,
            'termin3' => 0,
            'pelunasan' => 0,
        ]);
        Finance::factory()->create([
            'project_id' => $project2->id,
            'total' => 10000000,
            'dp' => 3000000,
            'termin1' => 0,
            'termin2' => 0,
            'termin3' => 0,
            'pelunasan' => 0,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'unpaid_invoices' => [
                        'count' => 2,
                    ],
                ],
            ]);
    }
}
