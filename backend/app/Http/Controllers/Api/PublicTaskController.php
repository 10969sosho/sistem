<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

/**
 * Endpoint API publik (tanpa auth) untuk dibaca oleh agent eksternal.
 * Read-only: hanya GET.
 */
class PublicTaskController extends Controller
{
    public function index(): JsonResponse
    {
        $tasks = Task::query()
            ->orderBy('deadline')
            ->orderBy('id')
            ->get(['id', 'title', 'status', 'cabang', 'deadline', 'finished_at', 'created_at', 'updated_at']);

        return response()->json([
            'data' => $tasks->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'condition' => $task->finished_at !== null
                    ? 'finished'
                    : ($task->status === 'todo' || $task->status === 'waiting' ? 'not_started' : 'on_going'),
                'owner' => $task->cabang !== null ? strtoupper($task->cabang) : null,
                'due_date' => $task->deadline?->format('Y-m-d'),
                'finished_at' => $task->finished_at?->toISOString(),
                'created_at' => $task->created_at?->toISOString(),
                'updated_at' => $task->updated_at?->toISOString(),
            ]),
        ]);
    }
}
