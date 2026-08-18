<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_lead_detail_without_customer(): void
    {
        $user = User::factory()->create();
        $lead = Lead::create([
            'name' => 'Lead Tanpa Customer',
            'phone' => '081234567890',
            'source' => 'whatsapp',
            'requirement' => 'Website company profile',
            'entered_at' => today(),
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/crm/leads/{$lead->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $lead->id)
            ->assertJsonPath('data.customer', null);
    }

    public function test_can_change_lead_status(): void
    {
        $user = User::factory()->create();
        $lead = Lead::create([
            'name' => 'Lead Status',
            'phone' => '081234567891',
            'source' => 'whatsapp',
            'requirement' => 'Landing page',
            'entered_at' => today(),
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/crm/leads/{$lead->id}/status", ['status' => 'interested']);

        $response->assertOk()
            ->assertJsonPath('data.status', 'interested')
            ->assertJsonPath('data.status_label', 'Interested');
        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'status' => 'interested']);
    }
}
