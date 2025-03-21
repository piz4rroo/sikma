<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Karena sudah ada middleware auth di routes
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'delivery_date' => ['required', 'date', 'after:today'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'delivery_date.required' => 'Tanggal pengiriman wajib diisi',
            'delivery_date.date' => 'Format tanggal pengiriman tidak valid',
            'delivery_date.after' => 'Tanggal pengiriman harus lebih dari hari ini',
            'delivery_address.required' => 'Alamat pengiriman wajib diisi',
            'delivery_address.max' => 'Alamat pengiriman maksimal 255 karakter',
            'notes.max' => 'Catatan maksimal 1000 karakter'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'delivery_date' => 'Tanggal Pengiriman',
            'delivery_address' => 'Alamat Pengiriman',
            'notes' => 'Catatan'
        ];
    }
}