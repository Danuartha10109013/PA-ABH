@extends('layouts.home', ['title' => 'Piutang'])

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
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <form action="{{ route('piutang.index') }}" method="GET">
                        <div class="form-floating mb-3" style="width: 20%;">
                            <input type="month" class="form-control" id="periode" name="periode" placeholder="Periode"
                                value="{{ request('periode') ?? date('Y-m') }}" onchange="this.form.submit()">
                            <label for="periode"><i class="bi bi-calendar"></i> Periode</label>
                        </div>
                    </form>
                    <table id="datatable" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="table-dark text-center" style="width: 2%">No</th>
                                <th class="table-primary" style="width: 10%">Collection ID</th>
                                <th class="table-primary" style="width: 10%">Amount</th>
                                <th class="table-primary" style="width: 10%">Due Date</th>
                                <th class="table-primary" style="width: 10%">From</th>
                                <th class="table-primary" style="width: 10%">Ext Reference</th>
                                <th class="table-primary" style="width: 10%">Payment Type</th>
                                <th class="table-primary" style="width: 10%">Status</th>
                                <th class="table-primary" style="width: 10%">Sisa Piutang</th>
                                <th class="table-primary not-print not-export-excel" style="width: 5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($piutang as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">
                                        {{ $item->collection_id }}
                                        <span style="cursor: pointer;"
                                            onclick="copyToClipboard(this, '{{ $item->collection_id }}')" title="Copy">
                                            <i class="ti ti-copy"></i>
                                        </span>
                                    </td>
                                    <td class="text-nowrap"> Rp. {{ number_format($item->amount, 0, ',', '.') }} </td>
                                    <td class="text-nowrap">
                                        {{ \Carbon\Carbon::parse($item->due_date)->locale('id')->translatedFormat('d F Y') }}
                                    </td>
                                    <td> {{ $item->payment_from ?? '-' }} </td>
                                    <td>
                                        {{ $item->ext_doc_ref ?? '-' }}
                                        @if (!empty($item->ext_doc_ref))
                                            <span style="cursor: pointer;"
                                                onclick="copyToClipboard(this, '{{ $item->ext_doc_ref }}')" title="Copy">
                                                <i class="ti ti-copy"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td> {{ $item->type == 'installment' ? 'Cicilan' : 'Pembayaran Penuh' }} </td>
                                    <td>
                                        <span class="badge bg-{{ $item->is_paid == 1 ? 'success' : 'danger' }}">
                                            {{ $item->is_paid == 1 ? 'Lunas' : 'Belum Lunas' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($item->type == 'installment')
                                            Rp. {{ number_format($item->installments->where('is_paid', false)->sum('amount'), 0, ',', '.') }}
                                        @else
                                            {{ $item->is_paid ? 'Rp. 0' : 'Rp. ' . number_format($item->amount, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="not-print not-export-excel">
                                        @if ($item->type == 'installment')
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <a href="#" class="btn btn-info btn-sm" title="Lihat Cicilan"
                                                    data-bs-toggle="modal" data-bs-target="#modalLihat{{ $item->collection_id }}">
                                                    <i class="ti ti-eye" style="font-size: 16x; color: #fff;"></i>
                                                </a>
                                            </div>
                                        @else
                                            <form action="{{ route('piutang.update-status', $item->collection_id) }}" method="post">
                                                @csrf
                                                <input type="text" name="is_paid" value="{{ $item->is_paid == 1 ? 0 : 1 }}" hidden>
                                                <button class="btn btn-sm btn-{{ $item->is_paid ? 'danger' : 'success' }}">
                                                    <i class="ti ti-status-change"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>




    {{-- Modal Edit --}}
    @foreach ($piutang as $item)
        <div class="modal fade modalLihat" id="modalLihat{{ $item->collection_id }}" data-bs-backdrop="static"
            data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalLihatLabel{{ $item->collection_id }}"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered  modal-xl">
                <div class="modal-content p-3">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalLihatLabel{{ $item->collection_id }}">Lihat
                            {{ $title }}
                        </h1>
                        <button type="button" class="custom-btn red close-modal p-3" style="width: 16px; height: 16px;"
                            data-bs-dismiss="modal" aria-label="Close">
                            <i class="ti ti-x" style="font-size: 16px; color: #fff;"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        @php
                            $installments = $item->installments;
                        @endphp
                        @if ($installments->count())
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($installments as $index => $cicilan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($cicilan->due_date)->translatedFormat('d F Y') }}</td>
                                            <td>Rp {{ number_format($cicilan->amount, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $cicilan->is_paid ? 'success' : 'danger' }}">
                                                    {{ $cicilan->is_paid ? 'Lunas' : 'Belum Lunas' }}
                                                </span>
                                            </td>
                                            <td>
                                                <form action="{{ route('piutang.installment.update-status', $cicilan->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="is_paid" value="{{ $cicilan->is_paid ? 0 : 1 }}">
                                                    <button class="btn btn-sm btn-{{ $cicilan->is_paid ? 'danger' : 'success' }}">
                                                        <i class="ti ti-status-change"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    {{-- Modal Edit --}}

    {{-- Modal Edit --}}
    @foreach ($piutang as $item)
        <div class="modal fade modalEdit" id="modalEdit{{ $item->collection_id }}" data-id="{{ $item->collection_id }}"
            data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="modalEditLabel{{ $item->collection_id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalEditLabel{{ $item->collection_id }}">Edit
                            {{ $title }}
                        </h1>
                        <button type="button" class="custom-btn red close-modal p-3" style="width: 16px; height: 16px;"
                            data-bs-dismiss="modal" aria-label="Close">
                            <i class="ti ti-x" style="font-size: 16px; color: #fff;"></i>
                        </button>
                    </div>
                    <form action="{{ route('piutang.update', $item->collection_id) }}" method="post"
                        class="needs-validation" novalidate enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="form_type" value="edit-{{ $item->collection_id }}">
                        <div class="modal-body">
                            <div class="mb-3">
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
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" id="ext_reference" name="ext_doc_ref"
                                    class="form-control @error('ext_doc_ref') is-invalid @enderror"
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
                                <label for="due_date">Due Date<span class="text-danger">*</span></label>
                                <input type="date" id="due_date"
                                    class="form-control @error('due_date') is-invalid @enderror" name="due_date"
                                    value="{{ old('due_date', \Carbon\Carbon::parse($item->due_date)->format('Y-m-d')) }}">
                                @error('due_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-floating">
                                <textarea class="form-control" placeholder="Note (Opsional)" id="note" name="note" style="height: 150px">{{ old('note', $item->note) }}</textarea>
                                <label for="note">Note (Opsional)</label>
                            </div>
                            @error('note')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

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
            @if ($errors->any() && old('form_type'))
                var formType = '{{ old('form_type') }}';
                if (formType.startsWith('edit-')) {
                    var errorEditId = formType.replace('edit-', '');
                    document.querySelectorAll('.modalEdit').forEach(function(modal) {
                        var modalInstance = new bootstrap.Modal(modal);
                        var id = modal.getAttribute('data-id');

                        if (id === errorEditId) {
                            modalInstance.show();
                        }

                        modal.addEventListener("hidden.bs.modal", function() {
                            window.location.reload();
                        });

                        modal.addEventListener("show.bs.modal", function() {
                            if (typeof resetValidation === 'function') {
                                resetValidation(modal);
                            }
                        });
                    });
                }
            @endif
        });


        $(document).ready(function() {
            $("#datatable").DataTable({});
        });
    </script>
@endpush
