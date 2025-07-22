@extends('layouts.home', ['title' => 'Money Out'])

@push('style')
    <style>
        .table-primary {
            background: #170061 !important;
            color: #fff !important;
        }

        .btn-primary {
            border-radius: 15px 0 15px 0;
        }

        .modal-content {
            border-radius: 20px 0 20px 0;
            padding: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        {{ $title }}
                    </h4>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBackdrop">
                        <i class="ti ti-plus"></i>
                        Catat {{ $subtitle }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <form action="{{ route('money-out.index') }}" method="GET">
                        <div class="form-floating mb-3" style="width: 20%;">
                            <input type="month" class="form-control" id="periode" name="periode" placeholder="Periode"
                                value="{{ request('periode') ?? date('Y-m') }}" onchange="this.form.submit()">
                            <label for="periode"><i class="bi bi-calendar"></i> Periode</label>
                        </div>
                    </form>

                    <table id="datatable" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="table-dark text-center">No</th>
                                <th class="table-primary nowrap">TRX ID</th>
                                <th class="table-primary">Category</th>
                                <th class="table-primary">Amount</th>
                                <th class="table-primary">Source</th>
                                <th class="table-primary">Payment To</th>
                                <th class="table-primary">Ext Reference</th>
                                <th class="table-primary not-print not-export-excel">Payment Proof</th>
                                <th class="table-primary">Payment Date</th>
                                <th class="table-primary">Payment Note</th>
                                <th class="table-primary">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($moneyout as $item)
                                @php
                                    $type = [
                                        0 => 'Cash',
                                        1 => 'Bank Transfer',
                                        2 => 'Other',
                                    ];
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">
                                        {{ $item->trx_id }}
                                        <span style="cursor: pointer;"
                                            onclick="copyToClipboard(this, '{{ $item->trx_id }}')" title="Copy">
                                            <i class="ti ti-copy"></i>
                                        </span>
                                    </td>
                                    <td> {{ $item->category->name }} </td>
                                    <td class="text-nowrap"> Rp. {{ number_format($item->amount, 0, ',', '.') }} </td>
                                    <td>{{ $type[$item->payment_method] ?? '-' }} </td>
                                    <td> {{ $item->payment_to ?? '-' }} </td>
                                    <td> {{ $item->ext_doc_ref ?? '-' }} </td>
                                    <td class="not-print not-export-excel">
                                        @if ($item->proof)
                                            <a href="{{ route('money-out.download', $item->proof) }}" target="_blank"
                                                class="btn btn-primary">
                                                <i class="ti ti-download"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td> {{ \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y') }}</td>
                                    <td> {{ $item->note ?? '-' }} </td>
                                    <td>
                                        <a href="#" class="custom-btn warning p-3" style="width: 34px; height:34px;"
                                            title="Edit" data-bs-toggle="modal"
                                            data-bs-target="#modalEdit{{ $item->trx_id }}">
                                            <i class="ti ti-edit" style="font-size: 20px; color: #000;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="modalBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalBackdropLabel">Catat {{ $subtitle }}</h1>
                    <button type="button" class="custom-btn red close-modal p-3" style="width: 16px; height: 16px;"
                        data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x" style="font-size: 16px; color: #fff;"></i>
                    </button>
                </div>
                <form action="{{ route('moneyout.store') }}" method="post" class="needs-validation" novalidate
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="form_type" value="tambah">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_id">Category<span class="text-danger">*</span></label>
                            <select name="category_id" class="select-add"
                                class="form-control @error('category_id') is-invalid @enderror">
                                <option></option>
                                @foreach ($category as $categorys)
                                    <option
                                        value="{{ $categorys->id }} {{ old('category_id') == $categorys->id ? 'selected' : '' }}">
                                        {{ $categorys->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="input-group has-validation mb-3">
                            <div class="form-floating is-invalid">
                                <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                    id="amount_idr" name="amount" placeholder="Amount" value="{{ old('amount') }}">
                                <label for="amount_idr">Amount</label>
                            </div>
                            <span class="input-group-text">IDR</span>
                            @error('amount')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="input-group has-validation mb-3">
                            <div class="form-floating is-invalid">
                                <input type="number" step="0.01"
                                    class="form-control @error('tax') is-invalid @enderror" id="tax_id" name="tax"
                                    placeholder="tax" value="{{ old('tax') }}">
                                <label for="tax_id">Pajak</label>
                            </div>
                            <span class="input-group-text">%</span>
                            @error('tax')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="input-group has-validation mb-3">
                            <div class="form-floating is-valid">
                                <input type="text" class="form-control" id="tax_result" placeholder="Nilai Pajak"
                                    readonly>
                                <label for="tax_result">Total Pajak</label>
                            </div>
                            <span class="input-group-text">IDR</span>
                        </div>



                        <div class="mb-3">
                            <label for="payment_method">Payment Method<span class="text-danger">*</span></label>
                            <select name="payment_method" class="select-add"
                                class="form-control @error('payment_method') is-invalid @enderror">
                                <option></option>
                                <option value="0">Cash</option>
                                <option value="1">Bank Transfer</option>
                                <option value="2">Other</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="proof" class="form-label">Proof (Opsional)</label>
                            <input type="file" class="form-control @error('proof') is-invalid @enderror"
                                id="proof" name="proof">
                            @error('proof')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="text-danger">Format upload hanya mendukung extension PDF dan maksimal upload
                                5MB</small>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" id="ext_reference"
                                class="form-control @error('ext_doc_ref') is-invalid @enderror" name="ext_doc_ref"
                                value="{{ old('ext_doc_ref') }}" placeholder="External Document Reference">
                            <label for="ext_reference">External Document Reference (Opsional)</label>
                            @error('ext_doc_ref')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" id="to_pay"
                                class="form-control @error('payment_to') is-invalid @enderror" name="payment_to"
                                value="{{ old('payment_to') }}" placeholder="Payment To">
                            <label for="to_pay">Payment To (Opsional)</label>
                            @error('payment_to')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pay_date">Payment Date<span class="text-danger">*</span></label>
                            <input type="date" id="pay_date"
                                class="form-control @error('payment_date') is-invalid @enderror" name="payment_date"
                                value="{{ old('payment_date') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                            @error('payment_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>


                        <div class="form-floating">
                            <textarea class="form-control" placeholder="Note (Opsional)" id="note" name="note" style="height: 150px"></textarea>
                            <label for="note">Note (Opsional)</label>
                        </div>

                        <div class="mb-3">
                            <input type="hidden" name="utang" value="0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="utang_id"
                                    name="utang" value="1" style="border: 0.5px solid #ccc"
                                    {{ old('utang') ? 'checked' : '' }}>
                                <label class="form-check-label" for="utang_id">Tambah Utang</label>
                            </div>
                        </div>


                        <div class="{{ old('utang') ? '' : 'd-none' }}" id="utang_box">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="modalBackdropLabel">Catat Utang</h1>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="input-group has-validation mb-3">
                                        <div class="form-floating is-invalid">
                                            <input type="number"
                                                class="form-control @error('amount_utang') is-invalid @enderror"
                                                id="amount_utang_idr" name="amount_utang" placeholder="Amount"
                                                value="{{ old('amount') }}" readonly>
                                            <label for="amount_utang_idr">Amount</label>
                                        </div>
                                        <span class="input-group-text">IDR</span>
                                        @error('amount_utang')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="type">Payment Type<span class="text-danger">*</span></label>
                                    <select name="type"
                                        class="form-control @error('type') is-invalid @enderror">
                                        <option selected disabled>Pilih Type Pembayaran</option>
                                        <option value="full">Pembayaran Penuh</option>
                                        <option value="installment">Cicilan</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="input-group has-validation mb-3 d-none" id="installment">
                                    <div class="form-floating is-invalid">
                                        <input type="number" step="1"
                                            class="form-control @error('installement_count') is-invalid @enderror"
                                            id="installement_count_id" name="installement_count"
                                            placeholder="installement_count" value="{{ old('installement_count') }}">
                                        <label for="installement_count_id">Jumlah Cicilan</label>
                                    </div>
                                    <span class="input-group-text">X</span>
                                    @error('installement_count')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="text" id="ext_reference" name="ext_doc_ref_utang"
                                        class="form-control @error('ext_doc_ref_utang') is-invalid @enderror"
                                        value="{{ old('ext_doc_ref_utang') }}" placeholder="External Document Reference">
                                    <label for="ext_reference">External Document Reference (Opsional)</label>
                                    @error('ext_doc_ref_utang')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="text" id="from_pay"
                                        class="form-control @error('payment_from_utang') is-invalid @enderror"
                                        name="payment_from_utang" value="{{ old('payment_from_utang') }}"
                                        placeholder="Payment From">
                                    <label for="from_pay">Payment From (Opsional)</label>
                                    @error('payment_from_utang')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="due_date_utang">Due Date<span class="text-danger">*</span></label>
                                    <input type="date" id="due_date_utang"
                                        class="form-control @error('due_date_utang') is-invalid @enderror"
                                        name="due_date_utang" value="{{ old('due_date_utang') }}">
                                    @error('due_date_utang')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Note (Opsional)" id="note_utang" name="note_utang"
                                        style="height: 150px"></textarea>
                                    <label for="note_utang">Note (Opsional)</label>
                                </div>
                                @error('note_utang')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Tambahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Tambah --}}

    {{-- Modal Edit --}}
    @foreach ($moneyout as $item)
        <div class="modal fade modalEdit" id="modalEdit{{ $item->trx_id }}" data-id="{{ $item->trx_id }}"
            data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="modalEditLabel{{ $item->trx_id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalEditLabel{{ $item->trx_id }}">Edit {{ $title }}
                        </h1>
                        <button type="button" class="custom-btn red close-modal p-3" style="width: 16px; height: 16px;"
                            data-bs-dismiss="modal" aria-label="Close">
                            <i class="ti ti-x" style="font-size: 16px; color: #fff;"></i>
                        </button>
                    </div>
                    <form action="{{ route('money-out.update', $item->trx_id) }}" method="post"
                        class="needs-validation" novalidate enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="form_type" value="edit-{{ $item->trx_id }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="category_id">Category<span class="text-danger">*</span></label>
                                <select name="category_id" class="select-edit"
                                    class="form-control @error('category_id') is-invalid @enderror">
                                    <option></option>
                                    @foreach ($category as $categorys)
                                        <option
                                            value="{{ $categorys->id }} {{ old('category_id') == $categorys->id ? 'selected' : '' }}"
                                            {{ $item->category_id == $categorys->id ? 'selected' : '' }}>
                                            {{ $categorys->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="input-group has-validation mb-3">
                                <div class="form-floating is-invalid">
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                        id="amount_idr" name="amount" placeholder="Amount"
                                        value="{{ old('amount', $item->amount) }}">
                                    <label for="amount_idr">Amount</label>
                                </div>
                                <span class="input-group-text">IDR</span>
                                @error('amount')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="input-group has-validation mb-3">
                                <div class="form-floating is-invalid">
                                    <input type="number" step="0.01"
                                        class="form-control @error('tax') is-invalid @enderror" id="tax_id"
                                        name="tax" placeholder="tax" value="{{ old('tax', $item->tax) }}">
                                    <label for="tax_id">Pajak</label>
                                </div>
                                <span class="input-group-text">%</span>
                                @error('tax')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            <div class=" mb-3">
                                <label for="payment_method">Payment Method<span class="text-danger">*</span></label>
                                <select name="payment_method" class="select-edit"
                                    class="form-control @error('payment_method') is-invalid @enderror">
                                    <option></option>
                                    <option value="0" {{ $item->payment_method == 0 ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="1" {{ $item->payment_method == 1 ? 'selected' : '' }}>Bank
                                        Transfer</option>
                                    <option value="2" {{ $item->payment_method == 2 ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="proof" class="form-label">Proof (Opsional)</label>
                                <input type="file" class="form-control @error('proof') is-invalid @enderror"
                                    id="proof" name="proof">
                                <small class="text-danger">Kosongkan jika tidak perlu, Format upload hanya mendukung
                                    extension PDF dan maksimal upload
                                    5MB</small>
                                @error('proof')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" id="ext_reference"
                                    class="form-control @error('ext_doc_ref') is-invalid @enderror" name="ext_doc_ref"
                                    value="{{ old('ext_doc_ref') }}" placeholder="External Document Reference">
                                <label for="ext_reference">External Document Reference (Opsional)</label>
                                @error('ext_doc_ref')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" id="to_pay"
                                    class="form-control @error('payment_to') is-invalid @enderror" name="payment_to"
                                    value="{{ old('payment_to', $item->payment_to) }}" placeholder="Payment To">
                                <label for="to_pay">Payment To (Opsional)</label>
                                @error('payment_to')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="pay_date">Payment Date<span class="text-danger">*</span></label>
                                <input type="date" id="pay_date"
                                    class="form-control @error('payment_date') is-invalid @enderror" name="payment_date"
                                    value="{{ old('payment_date', \Carbon\Carbon::parse($item->payment_date)->format('Y-m-d')) }}"
                                    max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                                @error('payment_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            <div class="form-floating">
                                <textarea class="form-control" placeholder="Note (Opsional)" id="note" name="note" style="height: 150px">{{ old('note', $item->note) }}</textarea>
                                <label for="note">Note (Opsional)</label>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary w-100">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    {{-- Modal Edit --}}
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('utang_id');
            const utangDiv = document.getElementById('utang_box');

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    utangDiv.classList.remove('d-none');
                } else {
                    utangDiv.classList.add('d-none');
                }
            });
        });

        function copyToClipboard(element, text) {
            navigator.clipboard.writeText(text).then(() => {
                let icon = element.querySelector('i');
                icon.classList.remove('ti-copy');
                icon.classList.add('ti-circle-check');
                setTimeout(() => {
                    icon.classList.remove('ti-circle-check');
                    icon.classList.add('ti-copy');
                }, 3000);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        }

        function resetValidation(modalElement) {
            const inputs = modalElement.querySelectorAll('.is-invalid');
            inputs.forEach(input => input.classList.remove('is-invalid'));

            const feedbacks = modalElement.querySelectorAll('.invalid-feedback');
            feedbacks.forEach(fb => fb.remove());
        }

        document.addEventListener("DOMContentLoaded", function() {
            var modalTambah = new bootstrap.Modal(document.getElementById('modalBackdrop'));
            @if (old('form_type') == 'tambah' && $errors->any())
                modalTambah.show();
            @endif
            document.getElementById('modalBackdrop').addEventListener("hidden.bs.modal", function() {
                window.location.reload();
            });
            document.querySelectorAll('.modalEdit').forEach(function(modal) {
                var modalInstance = new bootstrap.Modal(modal);

                var id = modal.getAttribute('data-id');

                @if ($errors->any() && old('form_type'))
                    var formType = '{{ old('form_type') }}';
                    if (formType.startsWith('edit-')) {
                        var errorEditId = formType.replace('edit-', '');
                        if (id === errorEditId) {
                            modalInstance.show();
                        }
                    }
                @endif

                modal.addEventListener("hidden.bs.modal", function() {
                    window.location.reload();
                });
                modal.addEventListener("show.bs.modal", function() {
                    resetValidation(document.getElementById('modalBackdrop'));
                });
            });
        });

        $(document).ready(function() {
            $("#datatable").DataTable({});
        });

        const amountInput = document.getElementById('amount_idr');
        const taxInput = document.getElementById('tax_id');
        const taxResult = document.getElementById('tax_result');

        function calculateTax() {
            const amount = parseFloat(amountInput.value) || 0;
            const taxPercent = parseFloat(taxInput.value) || 0;
            const taxAmount = (amount * taxPercent) / 100;

            // Tampilkan hasil ke field
            taxResult.value = taxAmount.toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR'
            });
        }

        amountInput.addEventListener('input', calculateTax);
        taxInput.addEventListener('input', calculateTax);

        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('pay_date');
            const today = new Date().toISOString().split('T')[0]; // Format: YYYY-MM-DD
            dateInput.max = today;
        });

        document.addEventListener("DOMContentLoaded", function() {
            const typeSelect = document.querySelector('select[name="type"]');
            const installmentGroup = document.getElementById('installment');

            function toggleInstallmentField() {
                if (typeSelect.value === 'installment') {
                    installmentGroup.classList.remove('d-none');
                } else {
                    installmentGroup.classList.add('d-none');
                }
            }

            toggleInstallmentField();

            typeSelect.addEventListener('change', toggleInstallmentField);
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Sinkronisasi amount utama ke amount utang
            const mainAmountInput = document.getElementById('amount_idr');
            const utangAmountInput = document.getElementById('amount_utang_idr');
            if(mainAmountInput && utangAmountInput) {
                function syncUtangAmount() {
                    utangAmountInput.value = mainAmountInput.value;
                }
                mainAmountInput.addEventListener('input', syncUtangAmount);
                syncUtangAmount();
            }
        });
    </script>
@endpush
