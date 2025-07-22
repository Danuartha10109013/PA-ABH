<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMoneyout extends FormRequest
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
            'payment_method' => 'required',
            'proof' => 'nullable|max:2048|mimes:png,jpg,pdf,doc,docx,xls,xlsx',
            'ext_doc_ref' => 'nullable|string',
            'payment_to' => 'nullable|string',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0|max:100',
        ];

        return $rules;
    }


    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',

            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.integer' => 'Jumlah pembayaran harus berupa angka.',
            'amount.min' => 'Jumlah pembayaran minimal harus 1.XXX',

            'payment_method.required' => 'Sumber pembayaran wajib dipilih.',
            'payment_method.string' => 'Sumber pembayaran harus berupa string.',

            'proof.max' => 'Bukti pembayaran tidak boleh lebih dari 2MB.',
            'proof.mimes' => 'Bukti pembayaran harus berupa file PNG, JPG, PDF, DOC, DOCX, XLS, atau XLSX.',

            'ext_doc_ref.string' => 'Referensi dokumen eksternal harus berupa teks.',

            'payment_to.string' => ' Tujuan pembayaran harus berupa teks.',

            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            'payment_date.date' => 'Format tanggal pembayaran tidak valid.',

            'note.string' => 'Catatan harus berupa teks.',

            'tax.numeric' => 'Pajak harus berupa angka.',
            'tax.max' => 'Pajak maksimal 100%',
        ];
    }
}
