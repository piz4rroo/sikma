<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PromoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Pastikan user terautentikasi dan memiliki izin yang sesuai
        return Auth::check() && $this->user()->can('manage promos');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Validasi tambahan untuk tipe diskon "percentage"
        if ($this->input('discount_type') === 'percentage') {
            $rules['discount_value'] = 'required|numeric|min:0|max:100';
        }

        // Validasi kode promo: unik untuk create, dan unik kecuali dirinya sendiri untuk update
        $promoId = $this->route('promo'); // Ambil ID promo jika ada
        $rules['code'] = [
            'nullable',
            'string',
            'max:20',
            Rule::unique('promos', 'code')->ignore($promoId),
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'Judul promo wajib diisi.',
            'discount_type.required' => 'Pilih jenis diskon.',
            'discount_type.in' => 'Jenis diskon harus "fixed" atau "percentage".',
            'discount_value.required' => 'Nilai diskon wajib diisi.',
            'discount_value.min' => 'Nilai diskon tidak boleh negatif.',
            'discount_value.max' => 'Diskon dalam persen tidak boleh lebih dari 100%.',
            'start_date.required' => 'Tanggal mulai promo wajib diisi.',
            'end_date.required' => 'Tanggal akhir promo wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
            'code.unique' => 'Kode promo ini sudah digunakan.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Gambar harus memiliki format: jpeg, png, jpg, atau gif.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $data = [];

        if ($this->filled('start_date')) {
            $data['start_date'] = date('Y-m-d H:i:s', strtotime($this->start_date));
        }

        if ($this->filled('end_date')) {
            $data['end_date'] = date('Y-m-d H:i:s', strtotime($this->end_date));
        }

        if ($this->filled('code')) {
            $data['code'] = strtoupper($this->code);
        }

        $this->merge($data);
    }
}
