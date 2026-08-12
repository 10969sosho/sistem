<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], 'phone' => ['required', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'],
            'source' => ['required', Rule::in(array_keys(Lead::SOURCES))], 'requirement' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string'], 'entered_at' => ['required', 'date'],
        ];
    }
}
