<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Endpoint API untuk agent eksternal (Hermes) & monitoring publik.
 * - GET: Read-only untuk melihat task.
 * - POST/PUT/PATCH: Memerlukan HERMES_API_KEY bila dikonfigurasi di environment.
 */
class PublicTaskController extends Controller
{
    public function __construct(private TaskService $taskService)
    {
    }

    /**
     * Verifikasi API Key jika HERMES_API_KEY dikonfigurasikan di .env.
     */
    protected function authorizeAgent(Request $request): void
    {
        $configuredKey = config('services.hermes.key') ?? env('HERMES_API_KEY');
        if (! empty($configuredKey)) {
            $providedKey = $request->header('X-API-KEY') ?? $request->bearerToken();
            if (! $providedKey || ! hash_equals((string) $configuredKey, (string) $providedKey)) {
                abort(response()->json([
                    'message' => 'Unauthorized: API Key Hermes tidak valid atau tidak disertakan.',
                ], 401));
            }
        }
    }

    /**
     * Format output task yang konsisten dan ramah agent/human.
     */
    private function transformTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority ?? 'medium',
            'type' => $task->type ?? 'development',
            'condition' => $task->finished_at !== null
                ? 'finished'
                : ($task->status === 'todo' || $task->status === 'waiting' ? 'not_started' : 'on_going'),
            'owner' => $task->cabang !== null ? strtoupper($task->cabang) : null,
            'cabang' => $task->cabang,
            'due_date' => $task->deadline?->format('Y-m-d'),
            'deadline' => $task->deadline?->format('Y-m-d'),
            'notes' => $task->notes,
            'pic' => $task->pic,
            'finished_at' => $task->finished_at?->format('Y-m-d\TH:i:sP'),
            'created_at' => $task->created_at?->format('Y-m-d\TH:i:sP'),
            'updated_at' => $task->updated_at?->format('Y-m-d\TH:i:sP'),
        ];
    }

    /**
     * Normalisasi payload (owner -> cabang, due_date -> deadline).
     */
    private function normalizePayload(array $input): array
    {
        if (isset($input['owner']) && ! isset($input['cabang'])) {
            $input['cabang'] = strtolower((string) $input['owner']);
        } elseif (isset($input['cabang'])) {
            $input['cabang'] = strtolower((string) $input['cabang']);
        }

        if (isset($input['due_date']) && ! isset($input['deadline'])) {
            $input['deadline'] = $input['due_date'];
        }

        return $input;
    }

    /**
     * Daftar task publik dengan filter opsional (owner/cabang, status, limit).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::query();

        if ($request->filled('owner')) {
            $query->where('cabang', strtolower($request->string('owner')));
        } elseif ($request->filled('cabang')) {
            $query->where('cabang', strtolower($request->string('cabang')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if (! $request->boolean('include_finished', true)) {
            $query->whereNull('finished_at');
        }

        $tasks = $query->orderBy('deadline')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $tasks->map(fn (Task $task) => $this->transformTask($task))->values(),
        ]);
    }

    /**
     * Detail satu task.
     */
    public function show(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        return response()->json([
            'data' => $this->transformTask($task),
        ]);
    }

    /**
     * Tambah task baru dari Hermes / external agent.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAgent($request);

        $input = $this->normalizePayload($request->all());

        $validator = Validator::make($input, [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'in:development,revisi,bug_fix,maintenance'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'status' => ['nullable', 'in:todo,progress,waiting,done'],
            'cabang' => ['nullable', 'in:tian,cecil'],
            'pic' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'estimate' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ], [
            'title.required' => 'Judul task wajib diisi.',
            'type.in' => 'Tipe task tidak valid.',
            'priority.in' => 'Prioritas task tidak valid.',
            'status.in' => 'Status task tidak valid.',
            'cabang.in' => 'Owner / Cabang harus tian atau cecil.',
            'deadline.date' => 'Format deadline / due_date tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi input gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['type'] = $data['type'] ?? 'development';
        $data['priority'] = $data['priority'] ?? 'medium';
        $data['status'] = $data['status'] ?? 'todo';

        $task = $this->taskService->create($data);

        return response()->json([
            'message' => 'Task berhasil ditambahkan.',
            'data' => $this->transformTask($task),
        ], 201);
    }

    /**
     * Update task dari Hermes / external agent.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizeAgent($request);

        $input = $this->normalizePayload($request->all());

        $validator = Validator::make($input, [
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:development,revisi,bug_fix,maintenance'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'status' => ['nullable', 'in:todo,progress,waiting,done'],
            'cabang' => ['nullable', 'in:tian,cecil'],
            'pic' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'estimate' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ], [
            'type.in' => 'Tipe task tidak valid.',
            'priority.in' => 'Prioritas task tidak valid.',
            'status.in' => 'Status task tidak valid.',
            'cabang.in' => 'Owner / Cabang harus tian atau cecil.',
            'deadline.date' => 'Format deadline / due_date tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi input gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = array_filter($validator->validated(), fn ($v) => $v !== null);
        $task = $this->taskService->update($id, $validated);

        return response()->json([
            'message' => 'Task berhasil diperbarui.',
            'data' => $this->transformTask($task),
        ]);
    }

    /**
     * Update status task secara instan (todo, progress, waiting, done).
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeAgent($request);

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:todo,progress,waiting,done'],
        ], [
            'status.required' => 'Status task wajib diisi.',
            'status.in' => 'Status task tidak valid (pilih: todo, progress, waiting, done).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi status gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = $this->taskService->changeStatus($id, $validator->validated()['status']);

        return response()->json([
            'message' => 'Status task berhasil diubah.',
            'data' => $this->transformTask($task),
        ]);
    }
}
