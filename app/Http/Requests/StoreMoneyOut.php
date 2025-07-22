<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreMoneyOut extends FormRequest
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
            'proof' => 'nullable|max:5048|mimes:pdf',
            'ext_doc_ref' => 'nullable|string',
            'payment_to' => 'nullable|string',
            'payment_date' => [
                'required',
                'date',
                'before_or_equal:' . Carbon::today()->toDateString(),
            ],
            'note' => 'nullable|string',
            'utang' => 'nullable|in:0,1',
            'ext_doc_ref_utang' => 'nullable|string',
            'payment_from_utang' => 'nullable|string',
            'note_utang' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0|max:100',
        ];

        if ($this->utang == 1) {
            $rules['amount_utang'] = 'required|integer|min:1';
            $rules['due_date_utang'] = 'required|date';
        }

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

            'amount_utang.required' => 'Jumlah Utang wajib diisi.',
            'amount_utang.integer' => 'Jumlah Utang harus berupa angka.',
            'amount_utang.min' => 'Jumlah Utang minimal harus 1.XXX',

            'ext_doc_ref_utang.string' => 'Referensi dokumen eksternal harus berupa teks.',

            'payment_from_utang.string' => 'Sumber pembayaran harus berupa teks.',

            'due_date_utang.required' => 'Tanggal Utang wajib diisi.',
            'due_date_utang.date' => 'Format tanggal Utang tidak valid.',

            'note_utang.string' => 'Catatan harus berupa teks.',

            'tax.numeric' => 'Pajak harus berupa angka.',
            'tax.max' => 'Pajak maksimal 100%',
        ];
    }
}
