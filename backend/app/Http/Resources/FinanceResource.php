<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'total' => (float) $this->total,
            'dp' => (float) $this->dp,
            'termin1' => (float) $this->termin1,
            'termin2' => (float) $this->termin2,
            'termin3' => (float) $this->termin3,
            'pelunasan' => (float) $this->pelunasan,
            'total_paid' => (float) $this->total_paid,
            'remaining' => (float) $this->remaining,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'project' => new ProjectResource($this->whenLoaded('project')),
        ];
    }
}
