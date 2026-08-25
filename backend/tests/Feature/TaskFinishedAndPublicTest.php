<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFinishedAndPublicTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_finished_tasks_are_hidden_from_default_list(): void
    {
        Task::factory()->create(['title' => 'Open task']);
        Task::factory()->create(['title' => 'Finished task', 'finished_at' => now()]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/tasks');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertContains('Open task', $titles);
        $this->assertNotContains('Finished task', $titles);
    }

    public function test_include_finished_returns_all_tasks(): void
    {
        Task::factory()->create(['finished_at' => now()]);
        Task::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/tasks?include_finished=1');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_finished_filter_returns_only_finished_tasks(): void
    {
        Task::factory()->create(['finished_at' => now()]);
        Task::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/tasks?finished=1');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertNotNull($response->json('data.0.finished_at'));
    }

    public function test_change_status_to_done_sets_finished_at_and_back_clears_it(): void
    {
        $task = Task::factory()->create(['status' => 'progress']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/tasks/{$task->id}/status", ['status' => 'done'])
            ->assertStatus(200);

        $task->refresh();
        $this->assertEquals('done', $task->status);
        $this->assertNotNull($task->finished_at);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/tasks/{$task->id}/status", ['status' => 'todo'])
            ->assertStatus(200);

        $task->refresh();
        $this->assertNull($task->finished_at);
    }

    public function test_cabang_filter_scopes_tasks_per_owner(): void
    {
        Task::factory()->create(['cabang' => 'cecil', 'title' => 'Cecil job']);
        Task::factory()->create(['cabang' => 'tian', 'title' => 'Tian job']);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/tasks?cabang=cecil&include_finished=1');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertContains('Cecil job', $titles);
        $this->assertNotContains('Tian job', $titles);
    }

    public function test_public_tasks_endpoint_is_accessible_without_auth(): void
    {
        Task::factory()->create(['status' => 'todo', 'cabang' => 'cecil']);
        Task::factory()->create(['status' => 'progress', 'cabang' => 'tian']);
        Task::factory()->create(['status' => 'done', 'cabang' => 'cecil', 'finished_at' => now()]);

        $response = $this->getJson('/api/public/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'status', 'condition', 'owner', 'due_date', 'finished_at'],
                ],
            ]);

        $conditions = collect($response->json('data'))->pluck('condition');
        $this->assertEqualsCanonicalizing(['not_started', 'on_going', 'finished'], $conditions->all());

        // Read-only: metode lain harus ditolak.
        $this->postJson('/api/public/tasks')->assertStatus(405);
    }
}
