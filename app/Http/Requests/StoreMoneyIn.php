<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreMoneyIn extends FormRequest
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
            'proof' => 'nullable|max:5048|mimes:pdf',
            'ext_doc_ref' => 'nullable|string',
            'payment_from' => 'nullable|string',
            'payment_date' => [
                'required',
                'date',
                'before_or_equal:' . Carbon::today()->toDateString(),
            ],
            'source' => 'required',
            'note' => 'nullable|string',
            'ext_doc_ref_piutang' => 'nullable|string',
            'payment_from_piutang' => 'nullable|string',
            'note_piutang' => 'nullable|string',
        ];
        if ($this->piutang == 1) {
            $rules['amount_piutang'] = 'required|integer|min:1';
            $rules['due_date_piutang'] = 'required|date';
        }
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

            'amount_piutang.required' => 'Jumlah Piutang wajib diisi.',
            'amount_piutang.integer' => 'Jumlah Piutang harus berupa angka.',
            'amount_piutang.min' => 'Jumlah Piutang minimal harus 1.XXX',

            'ext_doc_ref_piutang.string' => 'Referensi dokumen eksternal harus berupa teks.',

            'payment_from_piutang.string' => 'Sumber pembayaran harus berupa teks.',

            'due_date_piutang.required' => 'Tanggal Piutang wajib diisi.',
            'due_date_piutang.date' => 'Format tanggal Piutang tidak valid.',

            'note_piutang.string' => 'Catatan harus berupa teks.',
        ];
    }

}
