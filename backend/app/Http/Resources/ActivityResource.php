<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'lead_id' => $this->lead_id, 'type' => $this->type, 'description' => $this->description, 'created_at' => $this->created_at?->format('Y-m-d\TH:i:sP'), 'lead' => $this->whenLoaded('lead', fn () => ['id' => $this->lead->id, 'name' => $this->lead->name, 'status' => $this->lead->status])];
    }
}
