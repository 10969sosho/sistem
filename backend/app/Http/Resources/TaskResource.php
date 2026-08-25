<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'project_id' => $this->project_id,
            'lead_id' => $this->lead_id,
            'title' => $this->title,
            'type' => $this->type,
            'priority' => $this->priority,
            'status' => $this->status,
            'cabang' => $this->cabang,
            'finished_at' => $this->finished_at?->format('Y-m-d\TH:i:sP'),
            'is_finished' => $this->finished_at !== null,
            'pic' => $this->pic,
            'deadline' => $this->deadline?->format('Y-m-d'),
            'estimate' => $this->estimate,
            'notes' => $this->notes,
            'is_overdue' => $this->deadline !== null
                && $this->status !== 'done'
                && $this->deadline->isBefore(now()->startOfDay()),
            'created_at' => $this->created_at?->format('Y-m-d\TH:i:sP'),
            'updated_at' => $this->updated_at?->format('Y-m-d\TH:i:sP'),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'project' => new ProjectResource($this->whenLoaded('project')),
        ];
    }
}
