<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'pic_name' => $this->pic_name,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'address' => $this->address,
            'status' => $this->status,
            'notes' => $this->notes,
            'projects_count' => $this->whenCounted('projects'),
            'created_at' => $this->created_at?->format('Y-m-d\TH:i:sP'),
            'updated_at' => $this->updated_at?->format('Y-m-d\TH:i:sP'),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
        ];
    }
}
