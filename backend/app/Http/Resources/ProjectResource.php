<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'status' => $this->status,
            'deadline' => $this->deadline?->format('Y-m-d'),
            'pic' => $this->pic,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'finish_date' => $this->finish_date?->format('Y-m-d'),
            'internal_notes' => $this->internal_notes,
            'tasks_count' => $this->whenCounted('tasks'),
            'open_tasks_count' => $this->whenCounted('openTasks'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'hosting' => new HostingResource($this->whenLoaded('hosting')),
            'finance' => new FinanceResource($this->whenLoaded('finance')),
        ];
    }
}
