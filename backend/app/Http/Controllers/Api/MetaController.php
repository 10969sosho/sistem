<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class MetaController extends Controller
{
    /**
     * Options for dropdowns (customers, projects, PIC lists).
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'data' => [
                'customers' => Customer::query()
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]),
                'projects' => Project::query()
                    ->select('id', 'customer_id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($p) => ['value' => $p->id, 'label' => $p->name]),
                'pics' => [
                    'project' => Project::query()
                        ->whereNotNull('pic')
                        ->distinct()
                        ->pluck('pic'),
                    'task' => Task::query()
                        ->whereNotNull('pic')
                        ->distinct()
                        ->pluck('pic'),
                ],
            ],
        ]);
    }

    /**
     * Enum values used by the frontend (statuses, priorities, types).
     */
    public function enums(): JsonResponse
    {
        return response()->json([
            'data' => [
                'customer_status' => ['active', 'non_active'],
                'project_status' => ['pending', 'progress', 'testing', 'revisi', 'maintenance', 'selesai'],
                'task_status' => ['todo', 'progress', 'waiting', 'done'],
                'task_priority' => ['low', 'medium', 'high', 'urgent'],
                'task_type' => ['development', 'revisi', 'bug_fix', 'maintenance'],
                'task_cabang' => ['tian', 'cecil'],
                'ssl_status' => ['active', 'non_active'],
                'payment_status' => [
                    'belum_dp',
                    'dp_bayar',
                    'termin1_bayar',
                    'termin2_bayar',
                    'termin3_bayar',
                    'lunas',
                ],
            ],
        ]);
    }
}
