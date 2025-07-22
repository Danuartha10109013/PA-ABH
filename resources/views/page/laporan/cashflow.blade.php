{{-- filepath: d:\Work\Freelance\finance\resources\views\page\laporan\cashflow.blade.php --}}
@extends('layouts.home', ['title' => 'Laporan Cashflow'])

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
        
        .table-cashflow th,
        .table-cashflow td {
            border: 1px solid #000 !important;
            text-align: right;
            vertical-align: middle;
        }

        .table-cashflow th {
            background: #f8f9fa;
            text-align: center;
        }

        .table-cashflow td.text-left {
            text-align: left !important;
        }

        .saldo-awal {
            font-weight: bold;
            background: #e2e6ef;
        }

        .saldo-akhir {
            font-weight: bold;
            background: #d1ffd1;
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
    </style>
@endpush

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Laporan Cashflow</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('laporan.cashflow') }}" method="GET">
                    <div class="form-floating mb-3" style="width: 20%;">
                        <input type="month" class="form-control" id="periode" name="periode" placeholder="Periode"
                            value="{{ request('periode') ?? date('Y-m') }}" onchange="this.form.submit()">
                        <label for="periode"><i class="bi bi-calendar"></i> Periode</label>
                    </div>
                </form>
                <div class="mb-3">
                    <button class="export-btn" onclick="exportExcel()">Excel</button>
                    <button class="export-btn" onclick="exportPDF()">PDF</button>
                    <button class="export-btn" onclick="printDiv('cashflow-area')">Cetak</button>
                </div>
                <div id="cashflow-area">
                    <div class="letterhead">
                        <div class="letterhead-content">
                            <div style="display: flex; align-items: center;">
                                <div class="company-logo">
                                    <img src="{{ asset('assets/img/finance/tekmt.png') }}" alt="FMS Logo" onerror="this.style.display='none'; this.parentNode.innerHTML='<div class=\'company-logo-text\'>FMS</div>'">
                                </div>
                                <div class="company-info">
                                    <div class="company-name">PT Teknologi Mudah Terhubung</div>
                                    <div class="company-address">Jl. Cendana No. AE/55, Cigadung, Subang, Jawa Barat 41213</div>
                                    <div class="company-contact">Telp: 08996150000 | Email : hi@tappp.link </div>
                                </div>
                            </div>
                            <div class="document-info">
                                <div class="document-number">No. Dokumen: TEKMT/CF/{{ date('Ymd') }}/{{ rand(1000, 9999) }}</div>
                                <div class="document-date">Tanggal: {{ date('d F Y') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <table class="table table-bordered table-cashflow">
                        <thead>
                            <tr>
                                <th style="width:15%">Tanggal</th>
                                <th style="width:25%">Keterangan</th>
                                <th style="width:20%">Masuk (Rp)</th>
                                <th style="width:20%">Keluar (Rp)</th>
                                <th style="width:20%">Sisa (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="saldo-awal">
                                <td colspan="4" class="text-left">Saldo Awal</td>
                                <td>{{ number_format($saldo_awal, 0, ',', '.') }}</td>
                            </tr>
                            @php
                                $saldo = $saldo_awal;
                            @endphp
                            @foreach ($cashflows as $row)
                                @php
                                    $masuk = $row->type == 'in' ? $row->amount : 0;
                                    $keluar = $row->type == 'out' ? $row->amount : 0;
                                    $saldo += $masuk - $keluar;
                                @endphp
                                <tr>
                                    <td class="text-left">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                    <td class="text-left">{{ $row->description }}</td>
                                    <td>{{ $masuk ? number_format($masuk, 0, ',', '.') : '' }}</td>
                                    <td>{{ $keluar ? number_format($keluar, 0, ',', '.') : '' }}</td>
                                    <td>{{ number_format($saldo, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="saldo-akhir">
                                <td colspan="4" class="text-left">Saldo Akhir</td>
                                <td>{{ number_format($saldo, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function exportExcel() {
            var wb = XLSX.utils.table_to_book(document.querySelector('#cashflow-area table'), {
                sheet: "Sheet1"
            });
            XLSX.writeFile(wb, 'Laporan_Cashflow.xlsx');
        }

        function exportPDF() {
            var element = document.getElementById('cashflow-area');
            html2pdf().from(element).set({
                margin: 0.5,
                filename: 'Laporan_Cashflow.pdf',
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    orientation: 'landscape',
                    unit: 'in',
                    format: 'A4',
                    compressPDF: true
                }
            }).save();
        }

        function printDiv(divId) {
            var printContents = document.getElementById(divId).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
    </script>
@endpush
