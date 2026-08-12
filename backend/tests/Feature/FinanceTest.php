<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Finance;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
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

    public function test_can_get_finance_for_project(): void
    {
        $finance = Finance::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/finance/project/{$this->project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $finance->id,
                    'project_id' => $this->project->id,
                ],
            ]);
    }

    public function test_returns_null_when_project_has_no_finance(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/finance/project/{$this->project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data keuangan belum ada.',
                'data' => null,
            ]);
    }

    public function test_can_create_finance(): void
    {
        $data = [
            'project_id' => $this->project->id,
            'total' => 10000000,
            'dp' => 3000000,
            'termin1' => 2000000,
            'termin2' => 2000000,
            'termin3' => 1000000,
            'pelunasan' => 2000000,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance', $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data keuangan berhasil disimpan.',
                'data' => [
                    'total' => 10000000,
                    'total_paid' => 10000000,
                    'remaining' => 0,
                    'payment_status' => 'lunas',
                ],
            ]);

        $this->assertDatabaseHas('finances', $data);
    }

    public function test_can_update_finance(): void
    {
        $finance = Finance::factory()->create(['project_id' => $this->project->id]);

        $data = [
            'project_id' => $this->project->id,
            'total' => 15000000,
            'dp' => 5000000,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance', $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data keuangan berhasil disimpan.',
                'data' => [
                    'total' => 15000000,
                ],
            ]);

        $this->assertDatabaseHas('finances', $data);
    }

    public function test_can_delete_finance(): void
    {
        $finance = Finance::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/finance/project/{$this->project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data keuangan berhasil dihapus.',
            ]);

        $this->assertSoftDeleted('finances', ['id' => $finance->id]);
    }

    public function test_finance_validation_requires_project_id(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance', ['total' => 1000000]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_finance_validation_requires_total(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance', ['project_id' => $this->project->id]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }
}
