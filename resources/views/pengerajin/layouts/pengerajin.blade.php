<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Pengerajin')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    {{-- DataTables Bootstrap 5 (CSS saja, JS per-halaman kalau perlu) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --primary: #6366f1;
            --primary-soft: rgba(99, 102, 241, 0.12);
            --primary-soft-border: rgba(99, 102, 241, 0.35);

            --bg: #020617;
            /* body background */
            --content-bg: #020617;
            /* konten */
            --card: #0f172a;
            /* card background */
            --text: #e5e7eb;
            /* teks utama */
            --muted: #9ca3af;
            /* teks sekunder */
            --border: rgba(148, 163, 184, 0.25);

            --sidebar-bg-from: #020617;
            --sidebar-bg-to: #111827;
            --sidebar-text: #e5e7eb;
            --sidebar-muted: rgba(148, 163, 184, 0.9);
            --sidebar-active-bg: rgba(15, 23, 42, 0.85);
            --sidebar-active-text: #f9fafb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            padding-left: 260px;
            min-height: 100vh;
            color: var(--text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            inset-block: 0;
            inset-inline-start: 0;
            background: linear-gradient(160deg, var(--sidebar-bg-from), var(--sidebar-bg-to));
            color: var(--sidebar-text);
            padding: 20px 16px;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(15, 23, 42, 0.7);
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.5);
            border-radius: 999px;
        }

        .sidebar h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.8);
        }

        .sidebar h4 i {
            font-size: 20px;
        }

        .sidebar h6 {
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--sidebar-muted);
            margin: 16px 6px 8px;
            font-weight: 600;
        }

        .sidebar nav a {
            color: var(--sidebar-text);
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            margin-bottom: 4px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            position: relative;
            overflow: hidden;
            transition: background .18s, color .18s, padding-left .18s;
        }

        .sidebar nav a i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        .sidebar nav a::before {
            content: '';
            position: absolute;
            inset-inline-start: 0;
            inset-block: 0;
            width: 3px;
            background: var(--primary);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform .18s;
        }

        .sidebar nav a:hover {
            background: rgba(15, 23, 42, 0.9);
            padding-left: 16px;
        }

        .sidebar nav a:hover::before {
            transform: scaleY(1);
        }

        .sidebar nav a.active {
            background: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            font-weight: 600;
        }

        .sidebar nav a.active::before {
            transform: scaleY(1);
        }

        .sidebar form button {
            border-radius: 10px;
            font-size: 14px;
        }

        /* ================= CONTENT ================= */
        .content {
            padding: 24px;
            background: var(--content-bg);
            min-height: 100vh;
        }

        .content-inner {
            max-width: 1280px;
            margin: 0 auto;
        }

        .content-header {
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .content-header-title {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .content-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: #e5e7eb;
        }

        .content-header small {
            font-size: 13px;
            color: var(--muted);
        }

        .btn-sidebar-toggle {
            display: none;
        }

        /* ================= GENERIC CARD/BOX ================= */
        .box,
        .card-modern {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.6);
            padding: 18px;
            margin-bottom: 18px;
            color: var(--text);
        }

        .card-modern .card-header {
            padding: 10px 0 12px;
            margin-bottom: 8px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            background: transparent;
            color: var(--text);
        }

        .card-modern .card-title {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }

        .chart-box {
            height: 280px;
        }

        /* ================= METRIC / RINGKASAN ================= */
        .metric-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            font-weight: 600;
        }

        .metric-value {
            font-size: 24px;
            font-weight: 700;
            margin-top: 4px;
            color: #e5e7eb;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            border: 1px solid var(--primary-soft-border);
            background: var(--primary-soft);
            color: #c7d2fe;
        }

        /* ================= TABEL LAPORAN ================= */
        .table-dark-custom {
            background-color: var(--card);
            color: var(--text);
            border-radius: 12px;
            overflow: hidden;
        }

        .table-dark-custom thead tr {
            background-color: #020617;
        }

        .table-dark-custom thead th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            padding: 10px 12px !important;
        }

        .table-dark-custom tbody td {
            padding: 10px 12px !important;
            border-top: 1px solid rgba(148, 163, 184, 0.15);
            vertical-align: middle;
            font-size: 13px;
        }

        .table-dark-custom tbody tr:hover {
            background: rgba(15, 23, 42, 0.7);
        }

        /* ================= STATUS BADGE ================= */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .status-badge.status-baru {
            background: rgba(99, 102, 241, 0.12);
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, 0.4);
        }

        .status-badge.status-dibayar {
            background: rgba(59, 130, 246, 0.12);
            color: #bfdbfe;
            border-color: rgba(59, 130, 246, 0.4);
        }

        .status-badge.status-diproses {
            background: rgba(245, 158, 11, 0.12);
            color: #fed7aa;
            border-color: rgba(245, 158, 11, 0.4);
        }

        .status-badge.status-dikirim {
            background: rgba(6, 182, 212, 0.12);
            color: #a5f3fc;
            border-color: rgba(6, 182, 212, 0.4);
        }

        .status-badge.status-selesai {
            background: rgba(16, 185, 129, 0.12);
            color: #bbf7d0;
            border-color: rgba(16, 185, 129, 0.4);
        }

        .status-badge.status-dibatalkan {
            background: rgba(239, 68, 68, 0.12);
            color: #fecaca;
            border-color: rgba(239, 68, 68, 0.4);
        }

        /* ================= GRID DASHBOARD ================= */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 18px;
        }

        /* ================= FORM ================= */
        .form-control,
        .form-select {
            border-radius: 10px !important;
            font-size: 14px;
            border: 1px solid var(--border) !important;
            padding: 8px 12px;
            background-color: #020617;
            color: var(--text);
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.15rem rgba(99, 102, 241, 0.4);
            border-color: var(--primary) !important;
        }

        label {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 4px;
            font-weight: 500;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 992px) {
            body {
                padding-left: 0;
            }

            .sidebar {
                left: -260px;
            }

            .sidebar.active {
                left: 0;
            }

            .content {
                padding: 16px;
            }

            .content-header {
                flex-direction: row;
                align-items: center;
            }

            .content-header h1 {
                font-size: 20px;
            }

            .btn-sidebar-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 999px;
                border: 1px solid var(--border);
                background: #020617;
                color: var(--text);
            }

            .dashboard-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>

    @yield('css')
</head>

<body>

    <div class="sidebar d-flex flex-column">
        <h4 class="mb-3 text-center justify-content-center">
            <i class="fas fa-user-circle"></i>
            <span>{{ Auth::user()->pengerajin->nama_pengerajin ?? Auth::user()->username }}</span>
        </h4>

        <nav class="flex-grow-1">
            <a href="{{ route('pengerajin.profile') }}" class="{{ Route::is('pengerajin.profile') ? 'active' : '' }}">
                <i class="fas fa-user-edit"></i> Profil
            </a>
            <a href="{{ route('pengerajin.produk') }}" class="{{ Route::is('pengerajin.produk') ? 'active' : '' }}">
                <i class="fas fa-box-open"></i> Produk Anda
            </a>
            <a href="{{ route('pengerajin.produk-all') }}"
                class="{{ Route::is('pengerajin.produk-all') ? 'active' : '' }}">
                <i class="fas fa-globe"></i> Produk Semua
            </a>

            <h6>Laporan Usaha</h6>

            <a href="{{ route('pengerajin.laporan_usaha.index') }}"
                class="{{ Route::is('pengerajin.laporan_usaha.index') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Dashboard Laporan
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.transaksi') }}"
                class="{{ Route::is('pengerajin.laporan_usaha.transaksi') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i> Semua Transaksi
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.pendapatan-usaha') }}"
                class="{{ Route::is('pengerajin.laporan_usaha.pendapatan-usaha') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i> Pendapatan Per Usaha
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-terlaris') }}"
                class="{{ Route::is('pengerajin.laporan_usaha.produk-terlaris') ? 'active' : '' }}">
                <i class="fas fa-fire"></i> Produk Terlaris
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-slow-moving') }}"
                class="{{ Route::is('pengerajin.laporan_usaha.produk-slow-moving') ? 'active' : '' }}">
                <i class="fas fa-hourglass-half"></i> Produk Slow Moving
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.kategori-produk') }}"
                class="{{ Route::is('pengerajin.laporan_usaha.kategori-produk') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Kategori Produk
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-favorite') }}"
                class="{{ Route::is('pengerajin.laporan_usaha.produk-favorite') ? 'active' : '' }}">
                <i class="fas fa-heart"></i> Produk Favorite
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-views') }}"
                class="{{ Route::is('pengerajin.laporan_usaha.produk-views') ? 'active' : '' }}">
                <i class="fas fa-eye"></i> Produk Dilihat
            </a>
        </nav>

        <form action="{{ route('logout') }}" method="POST" class="mt-auto pt-3">
            @csrf
            <button class="btn btn-danger w-100">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </button>
        </form>
    </div>

    <div class="content">
        <div class="content-inner">
            <div class="content-header">
                <div class="content-header-title">
                    @hasSection('content_header')
                        @yield('content_header')
                    @else
                        <h1>@yield('title', 'Dashboard Pengerajin')</h1>
                        {{-- optional sub-title: bisa diisi dengan section lain kalau mau --}}
                    @endif
                </div>

                {{-- tombol sidebar hanya muncul di mobile --}}
                <button class="btn-sidebar-toggle d-lg-none" id="sidebarToggle" type="button">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            @yield('content')
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- jQuery (untuk DataTables & toggle kecil-kecilan) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        // Toggle sidebar di mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');

            if (sidebar && toggle) {
                toggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>

    @yield('js')
</body>

</html>
