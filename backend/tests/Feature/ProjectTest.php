<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create(['name' => 'Stable Customer']);
    }

    public function test_can_list_projects(): void
    {
        Project::factory(5)->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'status', 'deadline', 'customer'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_search_projects(): void
    {
        Project::factory()->create([
            'customer_id' => $this->customer->id,
            'name' => 'E-Commerce Website',
            'type' => 'Website',
            'description' => 'Online catalog project',
        ]);
        Project::factory()->create([
            'customer_id' => $this->customer->id,
            'name' => 'Mobile App',
            'type' => 'Mobile Application',
            'description' => 'Internal mobile project',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/projects?search=E-Commerce');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_projects_by_status(): void
    {
        Project::factory(3)->create([
            'customer_id' => $this->customer->id,
            'status' => 'progress',
        ]);
        Project::factory(2)->create([
            'customer_id' => $this->customer->id,
            'status' => 'revisi',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/projects?status=progress');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_single_project(): void
    {
        $project = Project::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $project->id,
                    'name' => $project->name,
                ],
            ]);
    }

    public function test_can_create_project(): void
    {
        $data = [
            'customer_id' => $this->customer->id,
            'name' => 'New Project',
            'type' => 'Website',
            'description' => 'Project description',
            'status' => 'progress',
            'deadline' => now()->addMonth()->format('Y-m-d'),
            'pic' => 'John Doe',
            'start_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/projects', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Project berhasil ditambahkan.',
                'data' => [
                    'name' => 'New Project',
                    'status' => 'progress',
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'customer_id' => $this->customer->id,
            'name' => 'New Project',
            'type' => 'Website',
            'status' => 'progress',
            'pic' => 'John Doe',
        ]);
    }

    public function test_can_update_project(): void
    {
        $project = Project::factory()->create(['customer_id' => $this->customer->id]);

        $data = [
            'name' => 'Updated Project',
            'status' => 'testing',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/projects/{$project->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Project berhasil diperbarui.',
                'data' => [
                    'name' => 'Updated Project',
                    'status' => 'testing',
                ],
            ]);

        $this->assertDatabaseHas('projects', $data);
    }

    public function test_can_delete_project(): void
    {
        $project = Project::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Project berhasil dihapus.',
            ]);

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_project_validation_requires_customer_id(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/projects', ['name' => 'Test']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_project_validation_requires_valid_customer(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/projects', [
                'customer_id' => 999,
                'name' => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }
}
