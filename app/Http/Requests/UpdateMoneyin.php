<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMoneyin extends FormRequest
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
        $rules = [
            'category_id' => 'required|exists:payments,id',
            'amount' => 'required|integer|min:1',
            'payment_method' => 'required|integer',
            'proof' => 'nullable|max:2048|mimes:png,jpg,pdf,doc,docx,xls,xlsx',
            'ext_doc_ref' => 'nullable|string',
            'payment_from' => 'nullable|string',
            'payment_date' => 'required|date',
            'source' => 'required',
            'note' => 'nullable|string',
        ];
        return $rules;
    }

    public function messages(): array
    {
        return [
            // 'trx_id.required' => 'ID transaksi wajib diisi.',
            // 'trx_id.regex' => 'Format ID transaksi tidak valid. Harus dalam format "IN-XXXXXXXXX" (9 digit angka).',
            // 'trx_id.unique' => 'ID transaksi sudah digunakan.',

            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',

            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.integer' => 'Jumlah pembayaran harus berupa angka.',
            'amount.min' => 'Jumlah pembayaran minimal harus 1.XXX',

            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.integer' => 'Metode pembayaran harus berupa angka.',

            'proof.max' => 'Bukti pembayaran tidak boleh lebih dari 2MB.',
            'proof.mimes' => 'Bukti pembayaran harus berupa file PNG, JPG, PDF, DOC, DOCX, XLS, atau XLSX.',

            'ext_doc_ref.string' => 'Referensi dokumen eksternal harus berupa teks.',

            'payment_from.string' => 'Sumber pembayaran harus berupa teks.',

            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            'payment_date.date' => 'Format tanggal pembayaran tidak valid.',

            'source.required' => 'Sumber Pemasukan wajib diisi.',

            'note.string' => 'Catatan harus berupa teks.',

        ];
    }
}
