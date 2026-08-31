<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_transactions(): void
    {
        Transaction::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/transactions');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_transaction(): void
    {
        $data = [
            'date' => '2026-08-15',
            'type' => 'pemasukan',
            'category' => 'Pendapatan Project',
            'description' => 'Project Test',
            'vendor' => 'Client A',
            'amount' => 5000000,
            'status' => 'paid',
            'payment_method' => 'Transfer',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/transactions', $data);

        $response->assertCreated()
            ->assertJsonFragment(['category' => 'Pendapatan Project']);

        $this->assertDatabaseHas('transactions', ['category' => 'Pendapatan Project', 'amount' => 5000000]);
    }

    public function test_can_show_transaction(): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/transactions/{$transaction->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $transaction->id]);
    }

    public function test_can_update_transaction(): void
    {
        $transaction = Transaction::factory()->create(['amount' => 100000]);

        $response = $this->actingAs($this->user)->putJson("/api/transactions/{$transaction->id}", [
            'date' => $transaction->date->format('Y-m-d'),
            'type' => $transaction->type,
            'category' => $transaction->category,
            'amount' => 200000,
            'status' => 'paid',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'amount' => 200000]);
    }

    public function test_can_delete_transaction(): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertOk();
        // SoftDeletes — row still exists but with deleted_at set
        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }

    public function test_can_get_summary(): void
    {
        Transaction::factory()->create(['type' => 'pemasukan', 'amount' => 1000000, 'status' => 'paid', 'date' => '2026-08-01']);
        Transaction::factory()->create(['type' => 'pengeluaran', 'amount' => 500000, 'status' => 'paid', 'date' => '2026-08-05']);

        $response = $this->actingAs($this->user)->getJson('/api/transactions/summary?year=2026&month=8');

        $response->assertOk()
            ->assertJsonFragment([
                'total_pemasukan' => 1000000.0,
                'total_pengeluaran' => 500000.0,
                'laba_rugi' => 500000.0,
            ]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/transactions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['date', 'type', 'category', 'amount']);
    }
}
