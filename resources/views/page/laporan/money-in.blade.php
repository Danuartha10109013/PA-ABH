@extends('layouts.home', ['title' => 'Laporan Money In'])

@push('style')
    <style>
        .letterhead {
            padding: 20px 0;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
        }

        .letterhead-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 20px;
            overflow: hidden;
        }

        .company-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        /* Fallback jika gambar tidak tersedia */
        .company-logo-text {
            font-weight: bold;
            font-size: 24px;
            color: #170061;
        }

        .company-info {
            flex-grow: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #170061;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 14px;
            color: #555;
            margin-bottom: 3px;
        }

        .company-contact {
            font-size: 14px;
            color: #555;
        }

        .document-info {
            text-align: right;
            padding-left: 20px;
        }

        .document-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .document-date {
            font-size: 14px;
            color: #555;
        }

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

        .export-btn {
            background: #f1f3fa;
            color: #222;
            border: 0.5px solid #ccc;
            border-radius: 16px 0px 16px 0px;
            padding: 12px 20px;
            margin-right: 8px;
            font-weight: 500;
            box-shadow: none;
            transition: background 0.2s;
        }

        .export-btn:hover {
            background: #e2e6ef;
            color: #111;
        }

        #moneyin-area {
            width: 100%;
            overflow-x: auto;
        }

        #moneyin-area table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            word-wrap: break-word;
        }

        #moneyin-area th,
        #moneyin-area td {
            font-size: 12px;
            padding: 6px;
            word-break: break-word;
        }

        .nowrap {
            white-space: nowrap;
        }

        /* Optional: force landscape if not already set in html2pdf */
        @media print {
            @page {
                size: A4 landscape;
            }
        }
    </style>
@endpush

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        Laporan Money In
                    </h4>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <form action="{{ route('laporan.money-in') }}" method="GET">
                        <div class="form-floating mb-3" style="width: 20%;">
                            <input type="month" class="form-control" id="periode" name="periode" placeholder="Periode"
                                value="{{ request('periode') ?? date('Y-m') }}" onchange="this.form.submit()">
                            <label for="periode"><i class="bi bi-calendar"></i> Periode</label>
                        </div>
                    </form>
                    {{-- <div class="mb-3">
                        <button class="export-btn" onclick="exportExcel()">Excel</button>
                        <button class="export-btn" onclick="exportPDF()">PDF</button>
                        <button class="export-btn" onclick="printDiv('moneyin-area')">Cetak</button>
                    </div> --}}

                    <div id="moneyin-area">
                        <div class="letterhead">
                            <div class="letterhead-content">
                                <div style="display: flex; align-items: center;">
                                    <div class="company-logo">
                                        <img src="{{ asset('assets/img/finance/tekmt.png') }}" alt="FMS Logo"
                                            onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'company-logo-text\'>FMS</div>'">
                                    </div>
                                    <div class="company-info">
                                        <div class="company-name">PT Teknologi Mudah Terhubung</div>
                                        <div class="company-address">Jl. Cendana No. AE/55, Cigadung, Subang, Jawa Barat 41213</div>
                                        <div class="company-contact">Telp: 08996150000 | Email : hi@tappp.link </div>
                                    </div>
                                </div>
                                <div class="document-info">
                                    <div class="document-number">No. Dokumen:
                                        TEKMT/CF/{{ date('Ymd') }}/{{ rand(1000, 9999) }}</div>
                                    <div class="document-date">Tanggal: {{ date('d F Y') }}</div>
                                </div>
                            </div>
                        </div>

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
                                    ]
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
                                        <td>{{ $source[$item->source] }} </td>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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


        $(document).ready(function() {
            $("#datatable").DataTable({
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: 'Excel',
                            className: 'custom-dt-button',
                            exportOptions: {
                                columns: ':not(.not-print):not(.not-export-excel)'
                            },
                            customize: function(xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                var rows = $('row', sheet);
                                $('cols col', sheet).each(function(index, col) {
                                    if (index === 0) $(col).attr('width', 5);
                                    if (index === 1) $(col).attr('width', 25);
                                    if (index === 2) $(col).attr('width', 15);
                                    if (index === 3) $(col).attr('width', 30);
                                });
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'PDF',
                            className: 'custom-dt-button',
                            exportOptions: {
                                columns: ':not(.not-print)'
                            },
                            orientation: 'potrait',
                            pageSize: 'A4',
                            customize: function(doc) {
                                if (!doc.content || !Array.isArray(doc.content) || doc.content.length === 0) {
                                    console.error('Struktur dokumen PDF tidak valid');
                                    return;
                                }

                                var companyName = $('.company-name').text() || 'PT. TAPPP';
                                var companyAddress = $('.company-address').text() || 'Jl. Keuangan Raya No. 123, Jakarta Selatan 12345';
                                var companyContact = $('.company-contact').text() || 'Telp: (021) 555-1234 | Email: info@tekmt.co.id';
                                var documentNumber = $('.document-number').text() || 'No. Dokumen: TEKMT/CF/' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '/' + Math.floor(1000 + Math.random() * 9000);
                                var documentDate = $('.document-date').text() || 'Tanggal: ' + new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});

                                doc.content.unshift({
                                    margin: [0, 0, 0, 20],
                                    table: {
                                        widths: ['70%', '30%'],
                                        body: [
                                            [{
                                                text: [
                                                    { text: companyName + '\n', style: 'companyName' },
                                                    { text: companyAddress + '\n', style: 'companyAddress' },
                                                    { text: companyContact, style: 'companyContact' }
                                                ],
                                                border: [false, false, false, false]
                                            },
                                            {
                                                text: [
                                                    { text: documentNumber + '\n', style: 'documentNumber' },
                                                    { text: documentDate, style: 'documentDate' }
                                                ],
                                                alignment: 'right',
                                                border: [false, false, false, false]
                                            }]
                                        ]
                                    },
                                    layout: 'noBorders'
                                });

                                // doc.content.unshift({
                                //     margin: [0, 0, 0, 10],
                                //     text: 'FMS',
                                //     style: 'logo',
                                //     alignment: 'center'
                                // });

                                doc.content.splice(2, 0, {
                                    canvas: [{ type: 'line', x1: 0, y1: 5, x2: 515, y2: 5, lineWidth: 1 }],
                                    margin: [40, 0, 40, 20]
                                });

                                doc.styles.companyName = {
                                    fontSize: 18,
                                    bold: true,
                                    color: '#170061'
                                };
                                doc.styles.companyAddress = {
                                    fontSize: 10,
                                    color: '#555'
                                };
                                doc.styles.companyContact = {
                                    fontSize: 10,
                                    color: '#555'
                                };
                                doc.styles.documentNumber = {
                                    fontSize: 12,
                                    bold: true
                                };
                                doc.styles.documentDate = {
                                    fontSize: 10,
                                    color: '#555'
                                };
                                doc.styles.logo = {
                                    fontSize: 24,
                                    bold: true,
                                    color: '#170061'
                                };

                                // Style untuk tabel
                                doc.styles.tableHeader = {
                                    alignment: 'center',
                                    bold: true,
                                    fontSize: 12,
                                    color: 'white',
                                    fillColor: '#1E2B37'
                                };

                                doc.defaultStyle.fontSize = 10;
                                doc.styles.tableBodyOdd.fillColor = "#f3f3f3";

                                if (doc.content.length > 3 && doc.content[3].table && doc.content[3].table.body) {
                                    doc.content[3].table.body.forEach(function(row, i) {
                                        row.forEach(function(cell, j) {
                                            if (j === 0 || j === 2 || j === 3) {
                                                cell.alignment = 'center';
                                            }
                                        });
                                    });

                                    doc.content[3].layout = {
                                        hLineWidth: function(i, node) {
                                            return i === 0 || i === node.table.body.length ? 2 : 0.5;
                                        },
                                        vLineWidth: function(i, node) {
                                            return 0.5;
                                        },
                                        hLineColor: function(i, node) {
                                            return i === 0 || i === node.table.body.length ? '#000000' : '#aaaaaa';
                                        },
                                        vLineColor: function(i, node) {
                                            return '#aaaaaa';
                                        }
                                    };

                                    let rows = doc.content[3].table.body;
                                    for (let i = 1; i < rows.length; i++) {
                                        if (i % 2 === 0) {
                                            rows[i].forEach(cell => {
                                                cell.fillColor = "#f3f3f3";
                                            });
                                        }
                                    }
                                }
                            }
                        },
                        {
                            extend: 'print',
                            text: 'Print',
                            className: 'custom-dt-button',
                            exportOptions: {
                                columns: ':not(.not-print)'
                            },
                            customize: function(win) {
                                var letterhead = $('.letterhead').clone();
                                $(win.document.body).prepend(letterhead);

                                $(win.document.head).append('<style>.letterhead{padding:20px 0;border-bottom:2px solid #333;margin-bottom:20px}.letterhead-content{display:flex;align-items:center;justify-content:space-between}.company-logo{width:80px;height:80px;background-color:#f8f9fa;display:flex;align-items:center;justify-content:center;border-radius:50%;margin-right:20px;overflow:hidden}.company-logo img{width:100%;height:100%;object-fit:contain;padding:5px}.company-logo-text{font-weight:bold;font-size:24px;color:#170061}.company-info{flex-grow:1}.company-name{font-size:24px;font-weight:bold;color:#170061;margin-bottom:5px}.company-address{font-size:14px;color:#555;margin-bottom:3px}.company-contact{font-size:14px;color:#555}.document-info{text-align:right;padding-left:20px}.document-number{font-size:16px;font-weight:bold;margin-bottom:5px}.document-date{font-size:14px;color:#555}</style>');
                            }
                        }
                    ]
            });
        });
    </script>
@endpush
