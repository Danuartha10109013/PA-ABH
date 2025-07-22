@extends('layouts.home', ['title' => 'Payment Category'])

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
                         Payment Category
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="table-dark text-center" style="width: 2%">No</th>
                                <th class="table-primary" style="width: 30%">Nama Kategori</th>
                                <th class="table-primary" style="width: 10%">Type</th>
                                <th class="table-primary not-export-excel" style="width: 10%">Last Update</th>
                                <th class="table-primary not-print not-export-excel" style="width: 5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $type = [
                                    0 => 'Money In',
                                    1 => 'Money Out',
                                ];
                            @endphp
                            @foreach ($payment as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td> {{ $item->name }} </td>
                                    <td class="{{ $item->type == 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $type[$item->type] }} </td>
                                    <td class="not-export-excel">
                                        {{ \Carbon\Carbon::parse($item->updated_at)->locale('id')->diffForHumans() }}
                                        oleh {{ $item->user->name }}
                                    </td>
                                    <td class="not-print not-export-excel">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <a href="#" class="custom-btn warning p-3"
                                                style="width: 34px; height: 34px;" title="Edit" data-bs-toggle="modal"
                                                data-bs-target="#modalEdit{{ $item->id }}">
                                                <i class="ti ti-edit" style="font-size: 20px; color: #000;"></i>
                                            </a>
                                        </div>
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
                    <h1 class="modal-title fs-5" id="modalBackdropLabel">Tambah {{ $title }}</h1>
                    <button type="button" class="custom-btn red close-modal p-3" style="width: 16px; height: 16px;"
                        data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x" style="font-size: 16px; color: #fff;"></i>
                    </button>
                </div>
                <form action="{{ route('payment.store') }}" method="post" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="form-floating mb-3">
                            <input type="text" id="name_category"
                                class="form-control @error('name') is-invalid @enderror" name="name"
                                value="{{ old('name') }}" placeholder="Name">
                            <label for="name_category">Name<span class="text-danger">*</span></label>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <select name="type" class="select-add"
                                class="form-control @error('type') is-invalid @enderror">
                                <option></option>
                                <option value="0">Money In (Pemasukan)</option>
                                <option value="1">Money Out (Pengeluaran)</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
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
    @foreach ($payment as $item)
        <div class="modal fade modalEdit" id="modalEdit{{ $item->id }}" data-bs-backdrop="static"
            data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalEditLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalEditLabel{{ $item->id }}">Edit {{ $title }}</h1>
                        <button type="button" class="custom-btn red close-modal p-3" style="width: 16px; height: 16px;"
                            data-bs-dismiss="modal" aria-label="Close">
                            <i class="ti ti-x" style="font-size: 16px; color: #fff;"></i>
                        </button>
                    </div>
                    <form action="{{ route('payment.update', $item->id) }}" method="post" class="needs-validation"
                        novalidate>
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <div class="modal-body">
                            <div class="form-floating mb-3">
                                <input type="text" id="name_category_{{ $item->id }}"
                                    class="form-control @error('name') is-invalid @enderror" name="name"
                                    value="{{ $item->name }}" placeholder="Name">
                                <label for="name_category_{{ $item->id }}">Name<span
                                        class="text-danger">*</span></label>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <select name="type" class="form-control @error('type') is-invalid @enderror"
                                    class="select-edit">
                                    <option></option>
                                    <option value="0" {{ $item->type == 0 ? 'selected' : '' }}>Money In (Pemasukan)
                                    </option>
                                    <option value="1" {{ $item->type == 1 ? 'selected' : '' }}>Money Out
                                        (Pengeluaran)
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
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


    {{-- Modal Hapus --}}
    @foreach ($payment as $item)
        <div class="modal fade" id="modalHapus{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false"
            tabindex="-1" aria-labelledby="modalHapusLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalHapusLabel{{ $item->id }}">Hapus {{ $title }}
                        </h1>
                        <button type="button" class="custom-btn red close-modal p-3" style="width: 16px; height: 16px;"
                            data-bs-dismiss="modal" aria-label="Close">
                            <i class="ti ti-x" style="font-size: 16px; color: #fff;"></i>
                        </button>
                    </div>
                    <form action="{{ route('payment.destroy', $item->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <div class="modal-body">
                            Apakah Anda yakin ingin menghapus data ini?
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Ya, Saya Yakin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    {{-- Modal Hapus --}}
@endsection

@push('script')
    <script>
        function resetValidation(modalElement) {
            const inputs = modalElement.querySelectorAll('.is-invalid');
            inputs.forEach(input => input.classList.remove('is-invalid'));

            const feedbacks = modalElement.querySelectorAll('.invalid-feedback');
            feedbacks.forEach(fb => fb.remove());
        }

        document.addEventListener("DOMContentLoaded", function() {
            var modalTambah = new bootstrap.Modal(document.getElementById('modalBackdrop'));
            @if ($errors->has('name') || $errors->has('type'))
                @if (old('id'))
                    var targetModal = document.getElementById('modalEdit{{ old('id') }}');
                    if (targetModal) {
                        var modalInstance = new bootstrap.Modal(targetModal);
                        modalInstance.show();
                    }
                @else
                    modalTambah.show();
                @endif
            @endif

            document.getElementById('modalBackdrop').addEventListener("hidden.bs.modal", function() {
                window.location.reload();
            });

            document.querySelectorAll('.modalEdit').forEach(function(modal) {
                modal.addEventListener("hidden.bs.modal", function() {
                    window.location.reload();
                });
                modal.addEventListener("show.bs.modal", function() {
                    resetValidation(modal);
                });
            });
        });


        $(document).ready(function() {
            $("#datatable").DataTable({
                // dom: 'Bfrtip',
                // buttons: [{
                //         extend: 'excelHtml5',
                //         text: 'Excel',
                //         className: 'custom-dt-button',
                //         exportOptions: {
                //             columns: ':not(.not-print):not(.not-export-excel)'
                //         },
                //         customize: function(xlsx) {
                //             var sheet = xlsx.xl.worksheets['sheet1.xml'];
                //             var rows = $('row', sheet);
                //             $('cols col', sheet).each(function(index, col) {
                //                 if (index === 0) $(col).attr('width', 5);
                //                 if (index === 1) $(col).attr('width', 25);
                //                 if (index === 2) $(col).attr('width', 15);
                //                 if (index === 3) $(col).attr('width', 30);
                //             });

                //             // rows.each(function() {
                //             //     $(this).find('c').each(function(index, cell) {
                //             //         if (index === 0 || index === 1 || index === 2) {
                //             //             $(cell).attr('s',
                //             //             '51'); // Center text alignment
                //             //         }
                //             //     });
                //             // });
                //         }
                //     },
                //     {
                //         extend: 'pdfHtml5',
                //         text: 'PDF',
                //         className: 'custom-dt-button',
                //         exportOptions: {
                //             columns: ':not(.not-print)'
                //         },
                //         orientation: 'landscape',
                //         pageSize: 'A4',
                //         customize: function(doc) {

                //             doc.styles.tableHeader = {
                //                 alignment: 'center',
                //                 bold: true,
                //                 fontSize: 12,
                //                 color: 'white',
                //                 fillColor: '#1E2B37'
                //             };

                //             doc.defaultStyle.fontSize = 10;
                //             doc.styles.tableBodyOdd.fillColor = "#f3f3f3";

                //             doc.content[1].table.widths = ['5%', '55%', '20%', '20%'];

                //             // Align text
                //             doc.content[1].table.body.forEach(function(row, i) {
                //                 row.forEach(function(cell, j) {
                //                     if (j === 0 || j === 2 || j === 3) {
                //                         cell.alignment = 'center';
                //                     }
                //                 });
                //             });
                //             doc.content[1].layout = {
                //                 hLineWidth: function(i, node) {
                //                     return i === 0 || i === node.table.body.length ? 2 :
                //                         0.5;
                //                 },
                //                 vLineWidth: function(i, node) {
                //                     return 0.5;
                //                 },
                //                 hLineColor: function(i, node) {
                //                     return i === 0 || i === node.table.body.length ?
                //                         '#000000' : '#aaaaaa';
                //                 },
                //                 vLineColor: function(i, node) {
                //                     return '#aaaaaa';
                //                 }
                //             };

                //             let rows = doc.content[1].table.body;
                //             for (let i = 1; i < rows.length; i++) {
                //                 if (i % 2 === 0) {
                //                     rows[i].forEach(cell => {
                //                         cell.fillColor =
                //                             "#f3f3f3";
                //                     });
                //                 }
                //             }
                //         }
                //     },
                //     {
                //         extend: 'print',
                //         text: 'Print',
                //         className: 'custom-dt-button',
                //         exportOptions: {
                //             columns: ':not(.not-print)'
                //         }
                //     }
                // ]
            });
        });
    </script>
@endpush
