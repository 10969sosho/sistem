<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmOpportunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_lead_options_only_include_leads_owned_by_user(): void
    {
        $user = User::factory()->create();
        $ownedLead = $this->createLead($user, 'Lead Milik User');
        $otherLead = $this->createLead(User::factory()->create(), 'Lead User Lain');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/crm/leads?per_page=100');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $ownedLead->id)
            ->assertJsonMissing(['id' => $otherLead->id]);
    }

    public function test_opportunity_cannot_be_created_for_another_users_lead(): void
    {
        $user = User::factory()->create();
        $otherLead = $this->createLead(User::factory()->create(), 'Lead User Lain');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/crm/opportunities', [
                'lead_id' => $otherLead->id,
                'title' => 'Penawaran Tidak Valid',
                'value' => 1000000,
                'offer_date' => today()->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('lead_id');
    }

    private function createLead(User $user, string $name): Lead
    {
        return Lead::create([
            'name' => $name,
            'phone' => '08123'.$user->id.str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
            'source' => 'whatsapp',
            'requirement' => 'Website company profile',
            'entered_at' => today(),
            'user_id' => $user->id,
        ]);
    }
}
