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
    }

    public function test_public_tasks_can_be_created_with_mapped_fields(): void
    {
        $response = $this->postJson('/api/public/tasks', [
            'title' => 'Task Hermes Barusan',
            'owner' => 'TIAN',
            'due_date' => '2026-09-20',
            'priority' => 'high',
            'notes' => 'Catatan dari hermes',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Task Hermes Barusan')
            ->assertJsonPath('data.owner', 'TIAN')
            ->assertJsonPath('data.cabang', 'tian')
            ->assertJsonPath('data.due_date', '2026-09-20')
            ->assertJsonPath('data.condition', 'not_started');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Task Hermes Barusan',
            'cabang' => 'tian',
            'deadline' => '2026-09-20 00:00:00',
        ]);
    }

    public function test_public_tasks_can_be_updated(): void
    {
        $task = Task::factory()->create([
            'title' => 'Original Task',
            'cabang' => 'cecil',
            'status' => 'todo',
        ]);

        $response = $this->patchJson("/api/public/tasks/{$task->id}", [
            'title' => 'Task Updated by Hermes',
            'owner' => 'TIAN',
            'priority' => 'urgent',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Task Updated by Hermes')
            ->assertJsonPath('data.owner', 'TIAN')
            ->assertJsonPath('data.priority', 'urgent');

        $task->refresh();
        $this->assertEquals('Task Updated by Hermes', $task->title);
        $this->assertEquals('tian', $task->cabang);
    }

    public function test_public_task_status_can_be_changed_and_syncs_finished_at(): void
    {
        $task = Task::factory()->create(['status' => 'progress']);

        $response = $this->patchJson("/api/public/tasks/{$task->id}/status", [
            'status' => 'done',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'done')
            ->assertJsonPath('data.condition', 'finished');
        $this->assertNotNull($response->json('data.finished_at'));

        $task->refresh();
        $this->assertEquals('done', $task->status);
        $this->assertNotNull($task->finished_at);

        // Ubah balik ke progress
        $responseBack = $this->patchJson("/api/public/tasks/{$task->id}/status", [
            'status' => 'progress',
        ]);

        $responseBack->assertStatus(200)
            ->assertJsonPath('data.status', 'progress')
            ->assertJsonPath('data.condition', 'on_going');
        $this->assertNull($responseBack->json('data.finished_at'));
    }

    public function test_public_task_write_enforces_api_key_when_configured(): void
    {
        config(['services.hermes.key' => 'secret-hermes-token-xyz']);

        // Tanpa key -> 401
        $this->postJson('/api/public/tasks', ['title' => 'Unauthorized Task'])
            ->assertStatus(401);

        // Key salah -> 401
        $this->withHeaders(['X-API-KEY' => 'wrong-key'])
            ->postJson('/api/public/tasks', ['title' => 'Unauthorized Task'])
            ->assertStatus(401);

        // Key benar via header X-API-KEY -> 201
        $this->withHeaders(['X-API-KEY' => 'secret-hermes-token-xyz'])
            ->postJson('/api/public/tasks', ['title' => 'Authorized Task', 'owner' => 'cecil'])
            ->assertStatus(201);

        $task = Task::where('title', 'Authorized Task')->first();

        // Key benar via Bearer token -> 200
        $this->withHeaders(['Authorization' => 'Bearer secret-hermes-token-xyz'])
            ->patchJson("/api/public/tasks/{$task->id}/status", ['status' => 'done'])
            ->assertStatus(200);
    }
}
