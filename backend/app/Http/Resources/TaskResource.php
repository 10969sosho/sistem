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
            'title' => $this->title,
            'type' => $this->type,
            'priority' => $this->priority,
            'status' => $this->status,
            'pic' => $this->pic,
            'deadline' => $this->deadline?->format('Y-m-d'),
            'estimate' => $this->estimate,
            'notes' => $this->notes,
            'is_overdue' => $this->deadline !== null
                && $this->status !== 'done'
                && $this->deadline->isBefore(now()->startOfDay()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'project' => new ProjectResource($this->whenLoaded('project')),
        ];
    }
}
