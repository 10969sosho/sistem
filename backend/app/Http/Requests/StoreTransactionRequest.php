<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'type' => ['required', 'in:pemasukan,pengeluaran'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:paid,pending,cancelled'],
            'payment_method' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'type.required' => 'Jenis transaksi wajib dipilih.',
            'type.in' => 'Jenis transaksi tidak valid.',
            'category.required' => 'Kategori wajib dipilih.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.min' => 'Nominal tidak boleh negatif.',
        ];
    }
}
