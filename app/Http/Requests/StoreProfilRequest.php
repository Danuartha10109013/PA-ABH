<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfilRequest extends FormRequest
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
            'name'=> 'required|string',
            'email'=> 'required|email',
            'no_phone' => 'required|string|regex:/^08[0-9]{8,13}$/',
            'photo'=> 'nullable|image|mimes:jpeg,png,jpg|max:5048',
            'password'=> 'nullable|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',

            'no_phone.required' => 'Nomor telepon wajib diisi.',
            'no_phone.string' => 'Nomor telepon harus berupa teks.',
            'no_phone.regex' => 'Format nomor telepon tidak valid contoh valid 08xxxxxxxx.',

            'photo.image' => 'Foto harus berupa gambar.',
            'photo.mimes' => 'Foto harus berupa file JPEG, PNG, atau JPG.',
            'photo.max' => 'Foto tidak boleh lebih dari 5MB.',

            'password.min'=> 'Password minimal harus 8 karakter.', 
        ];
    }
}
