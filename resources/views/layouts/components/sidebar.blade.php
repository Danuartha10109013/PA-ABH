<div class="sidebar" data-background-color="white" style="background-color: #0b4aa1 !important">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="white" style="background-color: #053a81 !important">
            <a href="{{ route('index') }}" class="logo d-flex align-items-center">
                <img src="{{ asset('assets/img/finance/tekmt.png') }}" alt="navbar brand" class="navbar-brand"
                    height="50" />
                <h2 class="fw-bold mt-2 ms-2" style="color:#80a3c0">TEKMT</h2>
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="ti ti-menu-2"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="ti ti-menu-2"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item {{ Route::is('index') ? 'active' : '' }}">
                    <a href="{{ route('index') }}" aria-expanded="false">
                        <i class="ti ti-dashboard-filled"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @if (Auth::user()->role == 'cfo')
                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-ellipsis-h"></i>
                        </span>
                        <h4 class="text-section text-white text-uppercase">Data Master</h4>
                    </li>
                    <li class="nav-item {{ Route::is('payment.index') ? 'active' : '' }}">
                        <a href="{{ route('payment.index') }}">
                            <i class="ti ti-tags"></i>
                            <p>Kelola Payment Category</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('money-in.index') ? 'active' : '' }}">
                        <a href="{{ route('money-in.index') }}">
                            <i class="ti ti-database-import"></i>
                            <p>Kelola Money In</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('money-out.index') ? 'active' : '' }}">
                        <a href="{{ route('money-out.index') }}">
                            <i class="ti ti-database-export"></i>
                            <p>Kelola Money Out</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('utang.index') ? 'active' : '' }}">
                        <a href="{{ route('utang.index') }}">
                            <i class="ti ti-package-export"></i>
                            <p>Kelola Utang</p>
                        </a>
                    </li>
                    <li class="nav-item {{ Route::is('piutang.index') ? 'active' : '' }}">
                        <a href="{{ route('piutang.index') }}">
                            <i class="ti ti-package-import"></i>
                            <p>Kelola Piutang</p>
                        </a>
                    </li>
                @endif
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section text-white text-uppercase">laporan</h4>
                </li>
                <li class="nav-item {{ Route::is('laporan.money-in') ? 'active' : '' }}">
                    <a href="{{ route('laporan.money-in') }}">
                        <i class="ti ti-file-import"></i>
                        <p>Laporan Money In</p>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('laporan.money-out') ? 'active' : '' }}">
                    <a href="{{ route('laporan.money-out') }}">
                        <i class="ti ti-file-export"></i>
                        <p>Laporan Money Out</p>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('laporan.utang') ? 'active' : '' }}">
                    <a href="{{ route('laporan.utang') }}">
                        <i class="ti ti-file-description"></i>
                        <p>Laporan Riwayat Utang</p>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('laporan.piutang') ? 'active' : '' }}">
                    <a href="{{ route('laporan.piutang') }}">
                        <i class="ti ti-file-description"></i>
                        <p>Laporan Riwayat Piutang</p>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('laporan.cashflow') ? 'active' : '' }}">
                    <a href="{{ route('laporan.cashflow') }}">
                        <i class="ti ti-file-analytics"></i>
                        <p>Laporan Cashflow</p>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('laporan.index') ? 'active' : '' }}">
                    <a href="{{ route('laporan.index') }}">
                        <i class="ti ti-file-analytics"></i>
                        <p>Laporan Neraca</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
