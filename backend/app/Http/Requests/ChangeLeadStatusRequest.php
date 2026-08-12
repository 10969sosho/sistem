<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeLeadStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(Lead::STATUSES)], 'estimated_value' => ['nullable', 'numeric', 'min:0'], 'deadline' => ['nullable', 'date'], 'lost_reason' => ['nullable', 'string', 'max:500']];
    }
}
