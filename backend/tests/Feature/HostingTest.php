<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Hosting;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $customer = Customer::factory()->create();
        $this->project = Project::factory()->create(['customer_id' => $customer->id]);
    }

    public function test_can_get_hosting_for_project(): void
    {
        $hosting = Hosting::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/hosting/project/{$this->project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $hosting->id,
                    'project_id' => $this->project->id,
                ],
            ]);
    }

    public function test_returns_null_when_project_has_no_hosting(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/hosting/project/{$this->project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data hosting belum ada.',
                'data' => null,
            ]);
    }

    public function test_can_create_hosting(): void
    {
        $data = [
            'project_id' => $this->project->id,
            'provider' => 'Niagahoster',
            'package' => 'Business',
            'expired_date' => now()->addYear()->format('Y-m-d'),
            'domain' => 'example.com',
            'registrar' => 'Niagahoster',
            'domain_expired_date' => now()->addYear()->format('Y-m-d'),
            'ssl_status' => 'active',
            'ssl_expired_date' => now()->addYear()->format('Y-m-d'),
            'server_ip' => '192.168.1.1',
            'panel' => 'cPanel',
            'username' => 'admin',
            'notes' => 'Test notes',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/hosting', $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data hosting berhasil disimpan.',
                'data' => [
                    'provider' => 'Niagahoster',
                    'domain' => 'example.com',
                ],
            ]);

        $this->assertDatabaseHas('hostings', [
            'project_id' => $this->project->id,
            'provider' => 'Niagahoster',
            'domain' => 'example.com',
        ]);
    }

    public function test_can_update_hosting(): void
    {
        $hosting = Hosting::factory()->create(['project_id' => $this->project->id]);

        $data = [
            'project_id' => $this->project->id,
            'provider' => 'Updated Provider',
            'domain' => 'updated.com',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/hosting', $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data hosting berhasil disimpan.',
                'data' => [
                    'provider' => 'Updated Provider',
                    'domain' => 'updated.com',
                ],
            ]);

        $this->assertDatabaseHas('hostings', $data);
    }

    public function test_can_delete_hosting(): void
    {
        $hosting = Hosting::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/hosting/project/{$this->project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data hosting berhasil dihapus.',
            ]);

        $this->assertSoftDeleted('hostings', ['id' => $hosting->id]);
    }

    public function test_hosting_validation_requires_project_id(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/hosting', ['provider' => 'Test']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }
}
