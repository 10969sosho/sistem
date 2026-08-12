<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Hosting;
use App\Models\Project;
use App\Models\Task;

class SearchService
{
    /**
     * Pencarian global: Customer, Project, Task, dan Domain.
     */
    public function search(string $query): array
    {
        $q = trim($query);

        if ($q === '') {
            return [
                'customers' => collect(),
                'projects' => collect(),
                'tasks' => collect(),
                'domains' => collect(),
            ];
        }

        $like = "%{$q}%";

        $customers = Customer::query()
            ->where('name', 'like', $like)
            ->orWhere('pic_name', 'like', $like)
            ->orWhere('whatsapp', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->select('id', 'name', 'pic_name', 'status')
            ->limit(10)
            ->get();

        $projects = Project::query()
            ->where('name', 'like', $like)
            ->orWhere('type', 'like', $like)
            ->orWhere('description', 'like', $like)
            ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like))
            ->with('customer:id,name')
            ->select('id', 'customer_id', 'name', 'status', 'type')
            ->limit(10)
            ->get();

        $tasks = Task::query()
            ->where('title', 'like', $like)
            ->orWhere('notes', 'like', $like)
            ->orWhere('pic', 'like', $like)
            ->orWhereHas('project', fn ($p) => $p->where('name', 'like', $like))
            ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like))
            ->with(['project:id,name', 'customer:id,name'])
            ->select('id', 'project_id', 'customer_id', 'title', 'status', 'priority', 'type')
            ->limit(10)
            ->get();

        $domains = Hosting::query()
            ->where('domain', 'like', $like)
            ->orWhere('provider', 'like', $like)
            ->orWhere('registrar', 'like', $like)
            ->orWhereHas('project', fn ($p) => $p->where('name', 'like', $like))
            ->with('project:id,name,customer_id')
            ->select('id', 'project_id', 'domain', 'provider', 'domain_expired_date')
            ->limit(10)
            ->get();

        return [
            'customers' => $customers,
            'projects' => $projects,
            'tasks' => $tasks,
            'domains' => $domains,
        ];
    }
}
