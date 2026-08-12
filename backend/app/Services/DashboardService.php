<?php

namespace App\Services;

use App\Models\Finance;
use App\Models\Hosting;
use App\Models\Project;
use App\Models\Task;

class DashboardService
{
    /**
     * Widget summary untuk dashboard.
     */
    public function summary(): array
    {
        $today = now()->toDateString();
        $endOfWeek = now()->addDays(7)->endOfDay()->toDateString();
        $expireWindowEnd = now()->addDays(30)->endOfDay()->toDateString();

        $taskBase = fn () => Task::query()->where('status', '!=', 'done');

        // Task hari ini
        $todayTasks = $taskBase()->whereDate('deadline', $today);

        // Task terlambat
        $overdueTasks = $taskBase()->whereDate('deadline', '<', $today);

        // Task minggu ini (termasuk hari ini)
        $weekTasks = $taskBase()->whereBetween('deadline', [$today, $endOfWeek]);

        // Project yang sedang berjalan
        $activeProjects = Project::query()->whereIn('status', ['progress', 'testing', 'revisi', 'maintenance']);

        // Project revisi
        $revisiProjects = Project::query()->where('status', 'revisi');

        // Hosting / domain akan expired (dalam 30 hari) atau sudah expired
        $hostingExpiring = Hosting::query()
            ->whereNotNull('expired_date')
            ->whereDate('expired_date', '<=', $expireWindowEnd)
            ->whereHas('project');

        $domainExpiring = Hosting::query()
            ->whereNotNull('domain_expired_date')
            ->whereDate('domain_expired_date', '<=', $expireWindowEnd)
            ->whereHas('project');

        // Tagihan belum lunas
        $unpaid = Finance::query()
            ->whereHas('project')
            ->whereRaw('(dp + termin1 + termin2 + termin3 + pelunasan) < total');

        return [
            'date' => $today,
            'today_tasks' => $this->widget($todayTasks, 'deadline', 8),
            'overdue_tasks' => $this->widget($overdueTasks, 'deadline', 8),
            'week_tasks' => $this->widget($weekTasks, 'deadline', 8),
            'active_projects' => $this->widget($activeProjects, 'deadline', 6),
            'revisi_projects' => $this->widget($revisiProjects, 'deadline', 6),
            'hosting_expiring' => $this->widget($hostingExpiring, 'expired_date', 6),
            'domain_expiring' => $this->widget($domainExpiring, 'domain_expired_date', 6),
            'unpaid_invoices' => $this->widget($unpaid, null, 6),
        ];
    }

    /**
     * Hitung jumlah + ambil sebagian item untuk sebuah widget.
     */
    private function widget($query, ?string $orderBy, int $limit): array
    {
        $query = clone $query;

        if ($orderBy) {
            $query->orderBy($orderBy, 'asc');
        }

        $modelClass = get_class($query->getModel());
        $withRelations = [];
        
        if (in_array($modelClass, [Task::class, Project::class])) {
            $withRelations = ['customer:id,name'];
        }

        return [
            'count' => $query->count(),
            'items' => $query
                ->with($withRelations)
                ->limit($limit)
                ->get(),
        ];
    }
}
