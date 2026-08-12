<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['type' => ['required', Rule::in(['whatsapp', 'call', 'meeting', 'email', 'note'])], 'description' => ['required', 'string', 'max:2000']]; }
}
