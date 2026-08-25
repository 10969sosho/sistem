<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'title' => $this->title, 'lead_id' => $this->lead_id, 'customer_id' => $this->customer_id, 'value' => (float) $this->value, 'stage' => $this->stage, 'probability' => $this->probability, 'offer_date' => $this->offer_date?->format('Y-m-d'), 'deal_date' => $this->deal_date?->format('Y-m-d'), 'notes' => $this->notes, 'created_at' => $this->created_at?->format('Y-m-d\TH:i:sP'), 'updated_at' => $this->updated_at?->format('Y-m-d\TH:i:sP'), 'lead' => $this->whenLoaded('lead', fn () => ['id' => $this->lead->id, 'name' => $this->lead->name, 'phone' => $this->lead->phone, 'status' => $this->lead->status]), 'customer' => $this->whenLoaded('customer', fn () => ['id' => $this->customer->id, 'name' => $this->customer->name])];
    }
}
