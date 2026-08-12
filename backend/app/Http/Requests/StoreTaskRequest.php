<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:development,revisi,bug_fix,maintenance'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:todo,progress,waiting,done'],
            'pic' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'estimate' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'type' => 'development',
            'priority' => 'medium',
            'status' => 'todo',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul task wajib diisi.',
            'type.in' => 'Tipe task tidak valid.',
            'priority.in' => 'Prioritas task tidak valid.',
            'status.in' => 'Status task tidak valid.',
        ];
    }
}
