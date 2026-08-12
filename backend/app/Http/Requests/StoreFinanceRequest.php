<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinanceRequest extends FormRequest
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
            'total' => ['required', 'numeric', 'min:0'],
            'dp' => ['nullable', 'numeric', 'min:0'],
            'termin1' => ['nullable', 'numeric', 'min:0'],
            'termin2' => ['nullable', 'numeric', 'min:0'],
            'termin3' => ['nullable', 'numeric', 'min:0'],
            'pelunasan' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['dp', 'termin1', 'termin2', 'termin3', 'pelunasan'] as $field) {
            $this->mergeIfMissing([$field => 0]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project wajib dipilih.',
            'project_id.exists' => 'Project yang dipilih tidak valid.',
            'total.required' => 'Total harga wajib diisi.',
            'total.min' => 'Total harga tidak boleh negatif.',
        ];
    }
}
