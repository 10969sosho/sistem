<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $today = now()->startOfDay();
        $within30 = now()->addDays(30)->endOfDay();

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'provider' => $this->provider,
            'package' => $this->package,
            'expired_date' => $this->expired_date?->format('Y-m-d'),
            'domain' => $this->domain,
            'registrar' => $this->registrar,
            'domain_expired_date' => $this->domain_expired_date?->format('Y-m-d'),
            'ssl_status' => $this->ssl_status,
            'ssl_expired_date' => $this->ssl_expired_date?->format('Y-m-d'),
            'server_ip' => $this->server_ip,
            'panel' => $this->panel,
            'username' => $this->username,
            'notes' => $this->notes,
            'hosting_status' => $this->expired_date
                ? ($this->expired_date->isBefore($today) ? 'expired' : ($this->expired_date->isBefore($within30) ? 'expiring' : 'active'))
                : null,
            'domain_status' => $this->domain_expired_date
                ? ($this->domain_expired_date->isBefore($today) ? 'expired' : ($this->domain_expired_date->isBefore($within30) ? 'expiring' : 'active'))
                : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'project' => new ProjectResource($this->whenLoaded('project')),
        ];
    }
}
