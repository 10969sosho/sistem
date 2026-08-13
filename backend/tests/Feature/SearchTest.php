<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Hosting;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_global_search_returns_all_types(): void
    {
        $customer = Customer::factory()->create(['name' => 'Acme Corp']);
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'name' => 'E-Commerce Website',
        ]);
        Task::factory()->create([
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'title' => 'Fix login bug',
        ]);
        Hosting::factory()->create([
            'project_id' => $project->id,
            'domain' => 'acme.com',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/search?q=Acme');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'customers',
                    'projects',
                    'tasks',
                    'domains',
                ],
            ]);
    }

    public function test_global_search_finds_customers(): void
    {
        Customer::factory()->create(['name' => 'Acme Corp']);
        Customer::factory()->create(['name' => 'Beta Inc']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/search?q=Acme');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.customers');
    }

    public function test_global_search_finds_projects(): void
    {
        $customer1 = Customer::factory()->create(['name' => 'Search Customer One']);
        $customer2 = Customer::factory()->create(['name' => 'Search Customer Two']);
        Project::factory()->create([
            'customer_id' => $customer1->id,
            'name' => 'E-Commerce Website',
            'type' => 'Website',
        ]);
        Project::factory()->create([
            'customer_id' => $customer2->id,
            'name' => 'Mobile App',
            'type' => 'Mobile App',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/search?q=E-Commerce');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.projects');
    }

    public function test_global_search_finds_tasks(): void
    {
        $customer = Customer::factory()->create();
        Task::factory()->create([
            'customer_id' => $customer->id,
            'title' => 'Fix login bug',
        ]);
        Task::factory()->create([
            'customer_id' => $customer->id,
            'title' => 'Add new feature',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/search?q=login');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.tasks');
    }

    public function test_global_search_finds_domains(): void
    {
        $customer = Customer::factory()->create();
        $project1 = Project::factory()->create(['customer_id' => $customer->id]);
        $project2 = Project::factory()->create(['customer_id' => $customer->id]);
        Hosting::factory()->create([
            'project_id' => $project1->id,
            'domain' => 'acme.com',
        ]);
        Hosting::factory()->create([
            'project_id' => $project2->id,
            'domain' => 'beta.com',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/search?q=acme');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.domains');
    }

    public function test_global_search_returns_empty_for_no_query(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/search?q=');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_global_search_returns_empty_for_no_matches(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/search?q=nonexistent');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'customers' => [],
                    'projects' => [],
                    'tasks' => [],
                    'domains' => [],
                ],
            ]);
    }
}
