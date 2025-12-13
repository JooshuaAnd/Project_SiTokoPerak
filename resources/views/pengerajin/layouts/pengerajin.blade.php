<!DOCTYPE html>
<html lang="id">

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard Pengerajin' }}</title>
    {{-- 1. BOOTSTRAP CSS (Wajib untuk grid dan form styling) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- 2. Font Awesome (untuk icon) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* CSS KUSTOM ANDA */
        body {
            background: #f5f5f5;
            /* Memberikan margin default ke body agar konten tidak tertutup sidebar */
            padding-left: 230px;
            min-height: 100vh;
            color: #212529; /* Default text color */
        }
        .sidebar {
            width: 230px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #343a40; /* Dark Mode */
            color: white;
            padding: 20px;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            margin-bottom: 6px;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s;
            font-size: 15px;
        }
        .sidebar a:hover {
            background: #495057;
            color: #ffffff;
        }
        /* Style untuk link yang sedang aktif */
        .sidebar a.active {
            background: #007bff; /* Warna biru Bootstrap primary */
            font-weight: bold;
        }

        .content {
            padding: 20px;
            flex-grow: 1; /* Konten mengisi sisa ruang */
        }

        /* Header yang meniru AdminLTE */
        .content-header {
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 20px;
        }

        /* Box container dasar yang meniru card AdminLTE */
        .box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            margin-bottom: 20px;
            padding: 20px;
        }

        /* Inject CSS dari child view */
        @yield('css')

    </style>
</head>
<body>

    {{-- SIDEBAR --}}
    <div class="sidebar d-flex flex-column">

        {{-- User Info --}}
        <h4 class="mb-3 text-center">
            <i class="fas fa-user-circle"></i>
            {{ Auth::user()->pengerajin->nama_pengerajin ?? Auth::user()->username }}
        </h4>
        <hr style="border-color: rgba(255, 255, 255, 0.2);">

        {{-- Menu Utama --}}
        <nav class="flex-grow-1">
            <a href="{{ route('pengerajin.profile') }}" class="{{ Route::is('pengerajin.profile') ? 'active' : '' }}">
                <i class="fas fa-user-edit me-2"></i> Profil
            </a>
            <a href="{{ route('pengerajin.produk') }}" class="{{ Route::is('pengerajin.produk') ? 'active' : '' }}">
                <i class="fas fa-box-open me-2"></i> Produk Anda
            </a>
            <a href="{{ route('pengerajin.produk-all') }}" class="{{ Route::is('pengerajin.produk-all') ? 'active' : '' }}">
                <i class="fas fa-globe me-2"></i> Produk Semua
            </a>

            <h6 class="mt-3 text-secondary">LAPORAN USAHA</h6>

            <a href="{{ route('pengerajin.laporan_usaha.index') }}" class="{{ Route::is('pengerajin.laporan_usaha.index') ? 'active' : '' }}">
                <i class="fas fa-chart-line me-2"></i> Dashboard Laporan
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.transaksi') }}" class="{{ Route::is('pengerajin.laporan_usaha.transaksi') ? 'active' : '' }}">
                <i class="fas fa-receipt me-2"></i> Semua Transaksi
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.pendapatan-usaha') }}" class="{{ Route::is('pengerajin.laporan_usaha.pendapatan-usaha') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave me-2"></i> Pendapatan Per Usaha
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-terlaris') }}" class="{{ Route::is('pengerajin.laporan_usaha.produk-terlaris') ? 'active' : '' }}">
                <i class="fas fa-fire me-2"></i> Produk Terlaris
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-slow-moving') }}" class="{{ Route::is('pengerajin.laporan_usaha.produk-slow-moving') ? 'active' : '' }}">
                <i class="fas fa-hourglass-half me-2"></i> Produk Slow Moving
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.kategori-produk') }}" class="{{ Route::is('pengerajin.laporan_usaha.kategori-produk') ? 'active' : '' }}">
                <i class="fas fa-tags me-2"></i> Kategori Produk
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-favorite') }}" class="{{ Route::is('pengerajin.laporan_usaha.produk-favorite') ? 'active' : '' }}">
                <i class="fas fa-heart me-2"></i> Produk Favorite
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-views') }}" class="{{ Route::is('pengerajin.laporan_usaha.produk-views') ? 'active' : '' }}">
                <i class="fas fa-eye me-2"></i> Produk Dilihat
            </a>
        </nav>

        {{-- Logout Button --}}
        <form action="{{ route('logout') }}" method="POST" class="mt-auto pt-3">
            @csrf
            <button class="btn btn-danger w-100">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </button>
        </form>
    </div>

    {{-- ISI HALAMAN --}}
    <div class="content">

        {{-- Header Konten (Optional, jika ingin judul halaman terpisah) --}}
        @hasSection('content_header')
            <div class="content-header">
                @yield('content_header')
            </div>
        @endif

        {{-- Konten Utama Dari Child View --}}
        @yield('content')
    </div>

    {{-- Script JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Inject JS dari child view --}}
    @yield('js')

</body>

</html>
