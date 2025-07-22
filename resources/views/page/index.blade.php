@extends('layouts.home', ['title' => 'Dashboard'])

@push('style')
    <style>
    </style>
@endpush

@section('content')
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Dashboard</h3>
            <h6 class="op-7 mb-2">Sistem Manajemen Keuangan TEKMT</h6>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0"
                style="background: linear-gradient(90deg, #6a7687 0%, #bfc5ce 100%); border-radius: 16px;">
                <div class="card-body">
                    <div>
                        <div class="text-white fw-bold mb-5 d-flex align-items-center justify-content-between">
                            Total Saldo Awal
                            <a href="">
                                <i class="ti ti-arrow-right fs-1 text-white"></i>
                            </a>
                        </div>
                        <div class="card w-100 border-0 shadow-sm" style="height: 100px;">
                            <div class="card-body">
                                <div class="fs-3 fw-bold ">Rp.
                                    {{ number_format($saldo->amount ?? 0, 0, ',', '.') }}</div>
                                <div class="-50 small">{{ $saldo->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0"
                style="background: linear-gradient(90deg, #6a7687 0%, #bfc5ce 100%); border-radius: 16px;">
                <div class="card-body">
                    <div>
                        <div class="text-white fw-bold mb-5 d-flex align-items-center justify-content-between">
                            Total Money In
                            <a href="">
                                <i class="ti ti-arrow-right fs-1 text-white"></i>
                            </a>
                        </div>
                        <div class="card w-100 border-0 shadow-sm" style="height: 100px;">
                            <div class="card-body">
                                <div class="fs-3 fw-bold ">Rp.
                                    {{ number_format($countMoneyIn->sum('amount') ?? 0, 0, ',', '.') }}</div>
                                <div class="-50 small">{{ $countMoneyIn->count() ?? 0 }} data from Money In</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0"
                style="background: linear-gradient(90deg, #6a7687 0%, #bfc5ce 100%); border-radius: 16px;">
                <div class="card-body">
                    <div>
                        <div class="text-white fw-bold mb-5 d-flex align-items-center justify-content-between">
                            Total Money Out
                            <a href="">
                                <i class="ti ti-arrow-right fs-1 text-white"></i>
                            </a>
                        </div>
                        <div class="card w-100 border-0 shadow-sm" style="height: 100px;">
                            <div class="card-body">
                                <div class="fs-3 fw-bold ">Rp.
                                    {{ number_format($countMoneyOut->sum('amount') ?? 0, 0, ',', '.') }}</div>
                                <div class="-50 small">{{ $countMoneyOut->count() ?? 0 }} data from Money Out</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0"
                style="background: #e74c3c; border-radius: 16px;">
                <div class="card-body">
                    <div>
                        <div class="text-white fw-bold mb-5 d-flex align-items-center justify-content-between">
                            Total Utang
                            <a href="">
                                <i class="ti ti-arrow-right fs-1 text-white"></i>
                            </a>
                        </div>
                        <div class="card w-100 border-0 shadow-sm" style="height: 100px;">
                            <div class="card-body">
                                <div class="fs-3 fw-bold ">Rp.
                                    {{ number_format($countUtang->sum('amount') ?? 0, 0, ',', '.') }}</div>
                                <div class="-50 small">{{ $countUtang->count() ?? 0 }} data from Utang</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0"
                style="background: #e74c3c; border-radius: 16px;">
                <div class="card-body">
                    <div>
                        <div class="text-white fw-bold mb-5 d-flex align-items-center justify-content-between">
                            Total Piutang
                            <a href="">
                                <i class="ti ti-arrow-right fs-1 text-white"></i>
                            </a>
                        </div>
                        <div class="card w-100 border-0 shadow-sm" style="height: 100px;">
                            <div class="card-body">
                                <div class="fs-3 fw-bold ">Rp.
                                    {{ number_format($countPiutang->sum('amount') ?? 0, 0, ',', '.') }}</div>
                                <div class="-50 small">{{ $countPiutang->count() ?? 0 }} data from Piutang</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <form method="GET" action="{{ route('index') }}" class="row mb-4">
                            <div class="col-md-4">
                                <label for="startMonth">Dari Bulan</label>
                                <input type="month" id="startMonth" name="startMonth" class="form-control"
                                    value="{{ request('startMonth', $startDate->format('Y-m')) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="endMonth">Sampai Bulan</label>
                                <input type="month" id="endMonth" name="endMonth" class="form-control"
                                    value="{{ request('endMonth', $endDate->format('Y-m')) }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-success me-2">Filter</button>
                                <a href="{{ route('index') }}" class="btn btn-danger">Reset</a>
                            </div>
                        </form>
                    </div>
                    <div class="card-category">
                        <h4>Total Keuangan Selama {{ $monthDiff }} Bulan</h4>
                        {{ $startDate->format('F Y') }} - {{ $endDate->format('F Y') }}
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                        <h1>Rp. {{ number_format($overalltotal, 0, ',', '.') }}</h1>
                    </div>
                    <div class="pull-in">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row card-tools-still-right">
                        <div class="card-title">Transaction History</div>
                        <div class="card-tools">
                            <div class="dropdown">
                                <button class="btn btn-icon btn-clean me-0" type="button" id="dropdownMenuButton"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <!-- Projects table -->
                        <table class="table align-items-center mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Payment Number</th>
                                    <th scope="col" class="text-end">Date & Time</th>
                                    <th scope="col" class="text-end">Amount</th>
                                    <th scope="col" class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">
                                        <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        Payment from #10231
                                    </th>
                                    <td class="text-end">Mar 19, 2020, 2.45pm</td>
                                    <td class="text-end">$250.00</td>
                                    <td class="text-end">
                                        <span class="badge badge-success">Completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        Payment from #10231
                                    </th>
                                    <td class="text-end">Mar 19, 2020, 2.45pm</td>
                                    <td class="text-end">$250.00</td>
                                    <td class="text-end">
                                        <span class="badge badge-success">Completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        Payment from #10231
                                    </th>
                                    <td class="text-end">Mar 19, 2020, 2.45pm</td>
                                    <td class="text-end">$250.00</td>
                                    <td class="text-end">
                                        <span class="badge badge-success">Completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        Payment from #10231
                                    </th>
                                    <td class="text-end">Mar 19, 2020, 2.45pm</td>
                                    <td class="text-end">$250.00</td>
                                    <td class="text-end">
                                        <span class="badge badge-success">Completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        Payment from #10231
                                    </th>
                                    <td class="text-end">Mar 19, 2020, 2.45pm</td>
                                    <td class="text-end">$250.00</td>
                                    <td class="text-end">
                                        <span class="badge badge-success">Completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        Payment from #10231
                                    </th>
                                    <td class="text-end">Mar 19, 2020, 2.45pm</td>
                                    <td class="text-end">$250.00</td>
                                    <td class="text-end">
                                        <span class="badge badge-success">Completed</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <button class="btn btn-icon btn-round btn-success btn-sm me-2">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        Payment from #10231
                                    </th>
                                    <td class="text-end">Mar 19, 2020, 2.45pm</td>
                                    <td class="text-end">$250.00</td>
                                    <td class="text-end">
                                        <span class="badge badge-success">Completed</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
@endsection
