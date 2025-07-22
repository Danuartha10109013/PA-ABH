@extends('layouts.home', ['title' => 'Money In'])

@push('style')
    <style>
        .table-primary {
            background: #170061 !important;
            color: #fff !important;
        }

        .btn-primary,
        .btn-warning {
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
                    <form action="{{ route('money-in.index') }}" method="GET">
                        <div class="d-flex gap-4 items-center">
                            <div class="form-floating mb-3">
                                <input type="month" class="form-control" id="periode" name="periode"
                                    placeholder="Periode" value="{{ request('periode') ?? date('Y-m') }}"
                                    onchange="this.form.submit()">
                                <label for="periode"><i class="bi bi-calendar"></i> Periode</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select name="category_id" id="category_id" class="form-select"
                                    onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @foreach ($category as $item)
                                        <option value="{{ $item->id }}"
                                            {{ request('category_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="category_id"><i class="bi bi-calendar"></i> Category</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select name="source" id="source" class="form-select" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="0" {{ request('source') === '0' ? 'selected' : '' }}>Individual
                                    </option>
                                    <option value="1" {{ request('source') === '1' ? 'selected' : '' }}>Organisasi
                                    </option>
                                </select>
                                <label for="source"><i class="bi bi-calendar"></i> Source</label>
                            </div>
                        </div>
                    </form>

                    <table id="datatable" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="table-dark text-center">No</th>
                                <th class="table-primary nowrap">TRX ID</th>
                                <th class="table-primary">Category</th>
                                <th class="table-primary">Amount</th>
                                <th class="table-primary">Method</th>
                                <th class="table-primary">Source</th>
                                <th class="table-primary">Payment From</th>
                                <th class="table-primary">Ext Reference</th>
                                <th class="table-primary not-print not-export-excel">Payment Proof</th>
                                <th class="table-primary">Payment Date</th>
                                <th class="table-primary">Payment Note</th>
                                <th class="table-primary">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $type = [
                                    0 => 'Cash',
                                    1 => 'Bank Transfer',
                                    2 => 'Other',
                                ];
                                $source = [
                                    0 => 'Individual',
                                    1 => 'Organisasi',
                                ];
                            @endphp
                            @foreach ($moneyin as $item)
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
                                    <td>{{ $type[$item->payment_method] }} </td>
                                    <td>{{ $source[$item->source] }}</td>
                                    <td> {{ $item->payment_from ?? '-' }} </td>
                                    <td> {{ $item->ext_doc_ref ?? '-' }} </td>
                                    <td class="not-print not-export-excel text-center">
                                        @if ($item->proof)
                                            <a href="{{ route('money-in.download', $item->proof) }}" target="_blank"
                                                class="btn btn-primary">
                                                <i class="ti ti-download"></i>
                                            </a>
                                        @else
                                            -
                                        @endif

                                    </td>
                                    <td> {{ \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y') }}</td>
                                    <td>{{ $item->note ?? '-' }}</td>
                                    <td>
                                        <a href="#" class="custom-btn warning p-3" style="width: 34px; height: 34px;"
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
                <form action="{{ route('moneyin.store') }}" method="post" class="needs-validation" novalidate
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
                            <label for="source">Sumber<span class="text-danger">*</span></label>
                            <select name="source" class="select-add"
                                class="form-control @error('source') is-invalid @enderror">
                                <option></option>
                                <option value="0">Individual</option>
                                <option value="1">Organisasi</option>
                            </select>
                            @error('source')
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
                            <input type="text" id="from_pay"
                                class="form-control @error('payment_from') is-invalid @enderror" name="payment_from"
                                value="{{ old('payment_from') }}" placeholder="Payment From">
                            <label for="from_pay">Payment From (Opsional)</label>
                            @error('payment_from')
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
                            <input type="hidden" name="piutang" value="0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="piutang_id"
                                    name="piutang" value="1" style="border: 0.5px solid #ccc"
                                    {{ old('piutang') ? 'checked' : '' }}>
                                <label class="form-check-label" for="piutang_id">Tambah Piutang</label>
                            </div>
                        </div>

                        <div class="{{ old('piutang') ? '' : 'd-none' }}" id="piutang_box">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="modalBackdropLabel">Catat Piutang</h1>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="input-group has-validation mb-3">
                                        <div class="form-floating is-invalid">
                                            <input type="number"
                                                class="form-control @error('amount_piutang') is-invalid @enderror"
                                                id="amount_piutang_idr" name="amount_piutang" placeholder="Amount"
                                                value="{{ old('amount') }}" readonly>
                                            <label for="amount_piutang_idr">Amount</label>
                                        </div>
                                        <span class="input-group-text">IDR</span>
                                        @error('amount_piutang')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="text" id="ext_reference" name="ext_doc_ref_piutang"
                                        class="form-control @error('ext_doc_ref_piutang') is-invalid @enderror"
                                        value="{{ old('ext_doc_ref_piutang') }}"
                                        placeholder="External Document Reference">
                                    <label for="ext_reference">External Document Reference (Opsional)</label>
                                    @error('ext_doc_ref_piutang')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="text" id="from_pay"
                                        class="form-control @error('payment_from_piutang') is-invalid @enderror"
                                        name="payment_from_piutang" value="{{ old('payment_from_piutang') }}"
                                        placeholder="Payment From">
                                    <label for="from_pay">Payment From (Opsional)</label>
                                    @error('payment_from_piutang')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="type_piutang">Payment Type<span class="text-danger">*</span></label>
                                    <select name="type_piutang" class="form-control @error('type_piutang') is-invalid @enderror" id="type_piutang">
                                        <option selected disabled>Pilih Type Pembayaran</option>
                                        <option value="full" {{ old('type_piutang') == 'full' ? 'selected' : '' }}>Pembayaran Penuh</option>
                                        <option value="installment" {{ old('type_piutang') == 'installment' ? 'selected' : '' }}>Cicilan</option>
                                    </select>
                                    @error('type_piutang')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="input-group has-validation mb-3 d-none" id="installment_piutang">
                                    <div class="form-floating is-invalid">
                                        <input type="number" step="1"
                                            class="form-control @error('installement_count_piutang') is-invalid @enderror"
                                            id="installement_count_piutang_id" name="installement_count_piutang"
                                            placeholder="installement_count_piutang" value="{{ old('installement_count_piutang') }}">
                                        <label for="installement_count_piutang_id">Jumlah Cicilan</label>
                                    </div>
                                    <span class="input-group-text">X</span>
                                    @error('installement_count_piutang')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="due_date_piutang">Due Date<span class="text-danger">*</span></label>
                                    <input type="date" id="due_date_piutang"
                                        class="form-control @error('due_date_piutang') is-invalid @enderror"
                                        name="due_date_piutang" value="{{ old('due_date_piutang') }}">
                                    @error('due_date_piutang')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Note (Opsional)" id="note_piutang" name="note_piutang"
                                        style="height: 150px"></textarea>
                                    <label for="note_piutang">Note (Opsional)</label>
                                </div>
                                @error('note_piutang')
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
    @foreach ($moneyin as $item)
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
                    <form action="{{ route('money-in.update', $item->trx_id) }}" method="post" class="needs-validation"
                        novalidate enctype="multipart/form-data">
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



                            <div class="mb-3">
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
                                <label for="source">Sumber<span class="text-danger">*</span></label>
                                <select name="source" class="select-edit"
                                    class="form-control @error('source') is-invalid @enderror">
                                    <option></option>
                                    <option value="0" {{ $item->source == 0 ? 'selected' : '' }}>Individual</option>
                                    <option value="1" {{ $item->source == 1 ? 'selected' : '' }}>Organisasi</option>
                                </select>
                                @error('source')
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
                                    value="{{ old('ext_doc_ref', $item->ext_doc_ref) }}"
                                    placeholder="External Document Reference">
                                <label for="ext_reference">External Document Reference (Opsional)</label>
                                @error('ext_doc_ref')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" id="from_pay"
                                    class="form-control @error('payment_from') is-invalid @enderror" name="payment_from"
                                    value="{{ old('payment_from', $item->payment_from) }}" placeholder="Payment From">
                                <label for="from_pay">Payment From (Opsional)</label>
                                @error('payment_from')
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
            const checkbox = document.getElementById('piutang_id');
            const piutangDiv = document.getElementById('piutang_box');
            const typeSelectPiutang = document.getElementById('type_piutang');
            const installmentGroupPiutang = document.getElementById('installment_piutang');

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    piutangDiv.classList.remove('d-none');
                } else {
                    piutangDiv.classList.add('d-none');
                }
            });

            function toggleInstallmentFieldPiutang() {
                if (typeSelectPiutang && installmentGroupPiutang) {
                    if (typeSelectPiutang.value === 'installment') {
                        installmentGroupPiutang.classList.remove('d-none');
                    } else {
                        installmentGroupPiutang.classList.add('d-none');
                    }
                }
            }
            if(typeSelectPiutang && installmentGroupPiutang) {
                toggleInstallmentFieldPiutang();
                typeSelectPiutang.addEventListener('change', toggleInstallmentFieldPiutang);
            }

            // Sinkronisasi amount utama ke amount piutang
            const mainAmountInput = document.getElementById('amount_idr');
            const piutangAmountInput = document.getElementById('amount_piutang_idr');
            if(mainAmountInput && piutangAmountInput) {
                function syncPiutangAmount() {
                    piutangAmountInput.value = mainAmountInput.value;
                }
                mainAmountInput.addEventListener('input', syncPiutangAmount);
                syncPiutangAmount();
            }
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
    </script>
@endpush
