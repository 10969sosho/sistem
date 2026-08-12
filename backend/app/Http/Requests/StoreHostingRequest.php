<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHostingRequest extends FormRequest
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
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'provider' => ['nullable', 'string', 'max:100'],
            'package' => ['nullable', 'string', 'max:100'],
            'expired_date' => ['nullable', 'date'],
            'domain' => ['nullable', 'string', 'max:255'],
            'registrar' => ['nullable', 'string', 'max:100'],
            'domain_expired_date' => ['nullable', 'date'],
            'ssl_status' => ['nullable', 'in:active,non_active'],
            'ssl_expired_date' => ['nullable', 'date'],
            'server_ip' => ['nullable', 'string', 'max:50'],
            'panel' => ['nullable', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project wajib dipilih.',
            'project_id.exists' => 'Project yang dipilih tidak valid.',
        ];
    }
}
