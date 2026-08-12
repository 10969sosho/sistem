<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['lead_id' => ['required', 'integer', 'exists:leads,id'], 'title' => ['required', 'string', 'max:255'], 'value' => ['required', 'numeric', 'min:0'], 'offer_date' => ['required', 'date'], 'notes' => ['nullable', 'string']]; }
}
