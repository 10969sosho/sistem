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
}
