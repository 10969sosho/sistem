<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebtRequest extends FormRequest
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
            'date' => ['nullable', 'date'],
            'type' => ['required', 'in:talangan,pinjaman,reimburse'],
            'person' => ['required', 'string', 'in:CECIL,TIAN'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:belum_dibayar,dibayar_sebagian,lunas'],
            'paid_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Jenis hutang wajib dipilih.',
            'type.in' => 'Jenis hutang tidak valid.',
            'person.required' => 'Nama orang wajib dipilih.',
            'person.in' => 'Nama orang tidak valid.',
            'description.required' => 'Keterangan wajib diisi.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.min' => 'Nominal tidak boleh negatif.',
        ];
    }
}
