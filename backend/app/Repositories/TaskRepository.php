<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

class TaskRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Task);
    }

    /**
     * Build a filtered & searchable query for tasks.
     *
     * @param  array{
     *     search?: string,
     *     status?: string,
     *     priority?: string,
     *     type?: string,
     *     customer_id?: int,
     *     project_id?: int,
     *     pic?: string,
     *     deadline_from?: string,
     *     deadline_to?: string,
     *     overdue?: bool,
     *     due_today?: bool,
     *     due_upcoming?: bool,
     *     include_finished?: bool,
     *     finished?: bool,
     * }  $filters
     */
    public function filter(array $filters): Builder
    {
        $query = $this->query()
            ->with(['customer:id,name,pic_name,whatsapp,email,status'])
            ->with(['project:id,name,status,customer_id', 'project.customer:id,name']);

        // Secara default, task yang sudah finish (sudah dicek) disembunyikan
        // dari list utama. Gunakan include_finished=1 untuk melihat semua,
        // atau finished=1 untuk hanya melihat yang sudah finish.
        if (! empty($filters['finished'])) {
            $query->whereNotNull('tasks.finished_at');
        } elseif (empty($filters['include_finished'])) {
            $query->whereNull('tasks.finished_at');
        }

        $this->applySearch($query, $filters['search'] ?? null);

        foreach (['status', 'priority', 'type', 'cabang', 'pic'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['deadline_from'])) {
            $query->whereDate('deadline', '>=', $filters['deadline_from']);
        }

        if (! empty($filters['deadline_to'])) {
            $query->whereDate('deadline', '<=', $filters['deadline_to']);
        }

        if (($filters['overdue'] ?? false) === true) {
            $query->whereDate('deadline', '<', now()->toDateString())
                ->where('status', '!=', 'done');
        }

        if (($filters['due_today'] ?? false) === true) {
            $query->whereDate('deadline', now()->toDateString())
                ->where('status', '!=', 'done');
        }

        if (($filters['due_upcoming'] ?? false) === true) {
            $query->whereBetween('deadline', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->where('status', '!=', 'done');
        }

        $sortBy = $filters['sort_by'] ?? 'deadline';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        return $this->applySort($query, $sortBy, $sortDir);
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('tasks.title', 'like', "%{$search}%")
                ->orWhere('tasks.notes', 'like', "%{$search}%")
                ->orWhere('tasks.pic', 'like', "%{$search}%")
                ->orWhereHas('project', fn (Builder $p) => $p->where('name', 'like', "%{$search}%"))
                ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', "%{$search}%"));
        });
    }

    protected function sortableColumns(): array
    {
        return ['title', 'status', 'priority', 'type', 'cabang', 'pic', 'deadline', 'created_at'];
    }
}
