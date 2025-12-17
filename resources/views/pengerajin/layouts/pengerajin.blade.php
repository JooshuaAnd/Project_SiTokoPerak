<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard Pengerajin' }}</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    {{-- DataTables Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --primary: #e5e6ee;
            --primary-light: #0313bc;
            --primary-dark: #c5c4cf;
            --bg: #1f6bb7;
            --card: #5495ff;
            --text: #1e293b;
            --muted: #f6f7f9;
            --border: #e2e8f0;
            --sidebar-bg: linear-gradient(135deg, #141a87 0%, #4f46e5 100%);
            --sidebar-text: #151515;
            --active: #f0f2f7;

            --status-baru: #6366f1;
            --status-dibayar: #3b82f6;
            --status-diproses: #f59e0b;
            --status-dikirim: #06b6d4;
            --status-selesai: #10b981;
            --status-dibatalkan: #ef4444;
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #111111 0%, #f1f5f9 100%);
            padding-left: 260px;
            min-height: 100vh;
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding: 24px 18px;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(233, 233, 236, 0.15);
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar h4 {
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: rgba(148, 140, 140, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .sidebar h4 i {
            font-size: 20px;
        }

        .sidebar h6 {
            color: rgba(255, 255, 255, 0.7) !important;
            font-size: 11px;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-top: 20px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            margin-bottom: 6px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }

        .sidebar a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 3px;
            height: 100%;
            background: var(--active);
            transform: scaleY(0);
            transform-origin: top;
        }

        .sidebar a i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            padding-left: 18px;
        }

        .sidebar a:hover::before {
            transform: scaleY(1);
        }

        .sidebar a.active {
            background: var(--active);
            color: var(--text);
            font-weight: 600;
        }

        /* Content */
        .content {
            padding: 28px;
        }

        .content-header {
            padding-bottom: 18px;
            border-bottom: 2px solid var(--border);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: var(--text);
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Card / Box */
        .box,
        .card-modern {
            background: #102544; /* Darker card background */
            border-radius: 15px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 18px;
        }

        .box:hover, /* Keep hover effect for consistency */
        .card-modern:hover { /* Keep hover effect for consistency */
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .card-modern .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* Lighter border for dark theme */
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, transparent 100%);
            color: #e8eef7; /* Ensure card header title is readable */
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        /* Grid dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 18px;
        }

        .chart-box {
            height: 300px;
        }

        /* Metric / ringkasan */
        .metric-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #b8ccdf; /* Lighter text for labels */
            font-weight: 600;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            margin-top: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            padding: .25rem .75rem;
            font-size: 12px;
            border-radius: 20px;
            border: 1.5px solid rgba(99, 102, 241, 0.3);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(129, 140, 248, 0.08) 100%);
            color: var(--primary);
            font-weight: 600;
        }

        /* Tabel laporan */
        .table-dark-custom {
            background-color: var(--card);
            color: #e8eef7; /* Light text for table content */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .table-dark-custom thead tr {
            background-color: #081327; /* Very dark background for table header */
        }

        .table-dark-custom thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* Lighter border */
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #b8ccdf; /* Light text for table headers */
            font-weight: 700;
            padding: 14px !important;
        }

        .table-dark-custom tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05); /* Very subtle border */
        }

        .table-dark-custom tbody tr:hover {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.03) 0%, transparent 100%);
        }

        .table-dark-custom tbody td {
            padding: 14px !important;
            vertical-align: middle;
        }

        /* Badge status transaksi */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: .3rem .75rem;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1.5px solid;
        }

        .status-badge.status-baru {
            background: rgba(99, 102, 241, 0.1);
            color: var(--status-baru);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .status-badge.status-dibayar {
            background: rgba(59, 130, 246, 0.1);
            color: var(--status-dibayar);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .status-badge.status-diproses {
            background: rgba(245, 158, 11, 0.1);
            color: var(--status-diproses);
            border-color: rgba(245, 158, 11, 0.3);
        }

        .status-badge.status-dikirim {
            background: rgba(6, 182, 212, 0.1);
            color: var(--status-dikirim);
            border-color: rgba(6, 182, 212, 0.3);
        }

        .status-badge.status-selesai {
            background: rgba(16, 185, 129, 0.1);
            color: var(--status-selesai);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .status-badge.status-dibatalkan {
            background: rgba(239, 68, 68, 0.1);
            color: var(--status-dibatalkan);
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* Toggle Tabel / Grafik */
        .toggle-pill {
            display: inline-flex;
            background: #0b1d39; /* Dark background for the toggle pill */
            border-radius: 25px;
            padding: 4px;
            gap: 4px;
        }

        .toggle-pill button {
            border: none;
            background: transparent;
            color: #032648; /* Muted text for inactive buttons */
            font-size: 13px;
            padding: 8px 18px;
            border-radius: 20px;
            outline: none;
            cursor: pointer;
            font-weight: 500;
        }

        .toggle-pill button.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: #ffffff; /* White text for active button */
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        /* Form */
        .form-control,
        .form-select {
            border-radius: 10px !important;
            font-size: 14px; /* Keep font size */
            border: 1px solid rgba(255, 255, 255, 0.1) !important; /* Lighter border */
            padding: 10px 14px;
            background-color: #0f233f; /* Slightly lighter than body for contrast */
            color: #e8eef7; /* Light text for input */
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
            border-color: var(--primary) !important; /* Primary color on focus */
        }
        label {
            font-size: 13px;
            color: #b8ccdf; /* Light text for labels */
            margin-bottom: 6px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 992px) {
            body {
                padding-left: 0;
            }

            .sidebar {
                position: fixed;
                width: 260px;
                left: -260px;
            }

            .sidebar.active {
                left: 0;
            }

            .content {
                padding: 16px;
            }

            .dashboard-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }

            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
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

            <h6 class="mt-3">LAPORAN USAHA</h6>

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
        <div class="content-header">
            <h1>{{ $title ?? 'Dashboard Pengerajin' }}</h1>
        </div>

        @yield('content')
    </div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
{{-- jQuery (for DataTables) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

@yield('js')
</body>

</html>
