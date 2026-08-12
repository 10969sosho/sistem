<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->project = Project::factory()->create(['customer_id' => $this->customer->id]);
    }

    public function test_can_list_tasks(): void
    {
        Task::factory(5)->create([
            'customer_id' => $this->customer->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'status', 'priority', 'type', 'deadline'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_search_tasks(): void
    {
        Task::factory()->create([
            'customer_id' => $this->customer->id,
            'title' => 'Fix login bug',
        ]);
        Task::factory()->create([
            'customer_id' => $this->customer->id,
            'title' => 'Add new feature',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks?search=login');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_tasks_by_status(): void
    {
        Task::factory(3)->create([
            'customer_id' => $this->customer->id,
            'status' => 'todo',
        ]);
        Task::factory(2)->create([
            'customer_id' => $this->customer->id,
            'status' => 'done',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks?status=todo');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_tasks_by_priority(): void
    {
        Task::factory(2)->create([
            'customer_id' => $this->customer->id,
            'priority' => 'high',
        ]);
        Task::factory(3)->create([
            'customer_id' => $this->customer->id,
            'priority' => 'low',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks?priority=high');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_tasks_by_type(): void
    {
        Task::factory(2)->create([
            'customer_id' => $this->customer->id,
            'type' => 'revisi',
        ]);
        Task::factory(3)->create([
            'customer_id' => $this->customer->id,
            'type' => 'development',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks?type=revisi');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_get_single_task(): void
    {
        $task = Task::factory()->create([
            'customer_id' => $this->customer->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                ],
            ]);
    }

    public function test_can_create_task(): void
    {
        $data = [
            'customer_id' => $this->customer->id,
            'project_id' => $this->project->id,
            'title' => 'New Task',
            'type' => 'development',
            'priority' => 'high',
            'status' => 'todo',
            'pic' => 'John Doe',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'estimate' => '2 hari',
            'notes' => 'Task notes',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Task berhasil ditambahkan.',
                'data' => [
                    'title' => 'New Task',
                    'priority' => 'high',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'customer_id' => $this->customer->id,
            'project_id' => $this->project->id,
            'title' => 'New Task',
            'type' => 'development',
            'priority' => 'high',
            'status' => 'todo',
            'pic' => 'John Doe',
        ]);
    }

    public function test_can_update_task(): void
    {
        $task = Task::factory()->create([
            'customer_id' => $this->customer->id,
            'project_id' => $this->project->id,
        ]);

        $data = [
            'title' => 'Updated Task',
            'type' => 'development',
            'priority' => 'urgent',
            'status' => 'progress',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Task berhasil diperbarui.',
                'data' => [
                    'title' => 'Updated Task',
                    'priority' => 'urgent',
                    'status' => 'progress',
                ],
            ]);

        $this->assertDatabaseHas('tasks', $data);
    }

    public function test_can_change_task_status(): void
    {
        $task = Task::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/tasks/{$task->id}/status", [
                'status' => 'done',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Status task berhasil diubah.',
                'data' => [
                    'status' => 'done',
                ],
            ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Task berhasil dihapus.',
            ]);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_task_validation_requires_title(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', ['priority' => 'high']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_task_validation_requires_valid_priority(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Test',
                'priority' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }
}
