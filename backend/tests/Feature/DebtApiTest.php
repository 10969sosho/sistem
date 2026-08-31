<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_debts(): void
    {
        Debt::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/debts');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_debt(): void
    {
        $data = [
            'type' => 'talangan',
            'person' => 'TIAN',
            'description' => 'Hosting payment',
            'amount' => 500000,
            'status' => 'belum_dibayar',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/debts', $data);

        $response->assertCreated()
            ->assertJsonFragment(['person' => 'TIAN']);

        $this->assertDatabaseHas('debts', ['person' => 'TIAN', 'amount' => 500000]);
    }

    public function test_can_show_debt(): void
    {
        $debt = Debt::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/debts/{$debt->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $debt->id]);
    }

    public function test_can_update_debt(): void
    {
        $debt = Debt::factory()->create(['status' => 'belum_dibayar']);

        $response = $this->actingAs($this->user)->putJson("/api/debts/{$debt->id}", [
            'type' => $debt->type,
            'person' => $debt->person,
            'description' => $debt->description,
            'amount' => $debt->amount,
            'status' => 'lunas',
            'paid_date' => '2026-08-31',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('debts', ['id' => $debt->id, 'status' => 'lunas']);
    }

    public function test_can_delete_debt(): void
    {
        $debt = Debt::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/debts/{$debt->id}");

        $response->assertOk();
        // SoftDeletes — row still exists but with deleted_at set
        $this->assertSoftDeleted('debts', ['id' => $debt->id]);
    }

    public function test_can_get_summary(): void
    {
        Debt::factory()->create(['person' => 'TIAN', 'amount' => 500000, 'status' => 'belum_dibayar']);
        Debt::factory()->create(['person' => 'CECIL', 'amount' => 200000, 'status' => 'lunas']);

        $response = $this->actingAs($this->user)->getJson('/api/debts/summary');

        $response->assertOk()
            ->assertJsonFragment([
                'total_all' => 700000.0,
            ]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/debts', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'person', 'description', 'amount']);
    }

    public function test_validates_person_enum(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/debts', [
            'type' => 'talangan',
            'person' => 'INVALID',
            'description' => 'Test',
            'amount' => 100000,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['person']);
    }
}
