<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOpportunityRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['title' => ['required', 'string', 'max:255'], 'value' => ['required', 'numeric', 'min:0'], 'stage' => ['required', Rule::in(Lead::STATUSES)], 'offer_date' => ['nullable', 'date'], 'notes' => ['nullable', 'string']]; }
}
