<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'company' => $this->company, 'email' => $this->email, 'phone' => $this->phone, 'source' => $this->source, 'source_label' => \App\Models\Lead::SOURCES[$this->source] ?? $this->source, 'requirement' => $this->requirement, 'notes' => $this->notes, 'entered_at' => $this->entered_at?->format('Y-m-d'), 'status' => $this->status, 'status_label' => \App\Models\Lead::STATUS_LABELS[$this->status] ?? $this->status, 'estimated_value' => $this->estimated_value !== null ? (float) $this->estimated_value : null, 'deadline' => $this->deadline?->format('Y-m-d'), 'deal_date' => $this->deal_date?->format('Y-m-d'), 'lost_reason' => $this->lost_reason, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString(), 'customer' => $this->whenLoaded('customer', fn () => $this->customer ? ['id' => $this->customer->id, 'name' => $this->customer->name] : null), 'opportunities' => OpportunityResource::collection($this->whenLoaded('opportunities')), 'activities' => ActivityResource::collection($this->whenLoaded('activities'))];
    }
}
