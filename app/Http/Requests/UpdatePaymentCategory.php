<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentCategory extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::unique('payments', 'name')->ignore($this->route('payment'))
            ],
            'type' => 'required',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Nama Kategori Wajib Diisi',
            'name.unique' => 'Nama Kategori Sudah Terdaftar',
            'type.required' => 'Type Kategori Wajib Diisi',
        ];
    }
}
