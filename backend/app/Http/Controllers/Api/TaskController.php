<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeTaskStatusRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(private TaskService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'status',
            'priority',
            'type',
            'customer_id',
            'project_id',
            'pic',
            'deadline_from',
            'deadline_to',
            'overdue',
            'due_today',
            'due_upcoming',
            'sort_by',
            'sort_dir',
            'per_page',
        ]);

        return TaskResource::collection($this->service->paginate($filters));
    }

    public function show(int $id): TaskResource
    {
        return new TaskResource($this->service->show($id));
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->service->create($request->validated());

        return response()->json([
            'message' => 'Task berhasil ditambahkan.',
            'data' => new TaskResource($this->service->show($task->id)),
        ], 201);
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $task = $this->service->update($id, $request->validated());

        return response()->json([
            'message' => 'Task berhasil diperbarui.',
            'data' => new TaskResource($this->service->show($task->id)),
        ]);
    }

    public function changeStatus(ChangeTaskStatusRequest $request, int $id): JsonResponse
    {
        $task = $this->service->changeStatus($id, $request->validated('status'));

        return response()->json([
            'message' => 'Status task berhasil diubah.',
            'data' => new TaskResource($this->service->show($task->id)),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Task berhasil dihapus.',
        ]);
    }
}
