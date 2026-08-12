<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_customers(): void
    {
        Customer::factory(5)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'pic_name', 'whatsapp', 'email', 'status'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_search_customers(): void
    {
        Customer::factory()->create(['name' => 'Acme Corp']);
        Customer::factory()->create(['name' => 'Beta Inc']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/customers?search=Acme');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_customers_by_status(): void
    {
        Customer::factory(3)->create(['status' => 'active']);
        Customer::factory(2)->create(['status' => 'non_active']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/customers?status=active');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_single_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                ],
            ]);
    }

    public function test_can_create_customer(): void
    {
        $data = [
            'name' => 'New Customer',
            'pic_name' => 'John Doe',
            'whatsapp' => '081234567890',
            'email' => 'john@example.com',
            'address' => 'Jl. Test No. 123',
            'status' => 'active',
            'notes' => 'Test notes',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/customers', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Customer berhasil ditambahkan.',
                'data' => [
                    'name' => 'New Customer',
                    'email' => 'john@example.com',
                ],
            ]);

        $this->assertDatabaseHas('customers', $data);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::factory()->create();

        $data = [
            'name' => 'Updated Customer',
            'status' => 'non_active',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/customers/{$customer->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Customer berhasil diperbarui.',
                'data' => [
                    'name' => 'Updated Customer',
                    'status' => 'non_active',
                ],
            ]);

        $this->assertDatabaseHas('customers', $data);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Customer berhasil dihapus.',
            ]);

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_customer_validation_requires_name(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/customers', ['status' => 'active']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_customer_validation_requires_valid_status(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/customers', [
                'name' => 'Test',
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
