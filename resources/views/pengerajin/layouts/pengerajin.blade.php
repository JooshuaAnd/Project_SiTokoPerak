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

    <style>
        :root{
            --bg: #f4f6f9;          /* mirip AdminLTE */
            --card: #ffffff;
            --text: #212529;
            --muted: #6c757d;
            --border: #e9ecef;
            --sidebar-bg: #ffffff;  /* sidebar putih */
            --sidebar-text: #495057;
            --active: #0d6efd;      /* bootstrap primary */
        }

        body{
            background: var(--bg);
            padding-left: 230px;
            min-height: 100vh;
            color: var(--text);
        }

        /* Sidebar putih */
        .sidebar{
            width: 230px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding: 18px;
            z-index: 1000;
            border-right: 1px solid var(--border);
            box-shadow: 2px 0 8px rgba(0,0,0,.05);
            overflow-y: auto;
        }

        .sidebar h4{
            color: var(--text);
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .sidebar h6{
            color: var(--muted) !important;
            font-size: 12px;
            letter-spacing: .04em;
        }

        .sidebar hr{
            border-color: var(--border) !important;
            opacity: 1;
        }

        .sidebar a{
            color: var(--sidebar-text);
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            margin-bottom: 6px;
            border-radius: 8px;
            text-decoration: none;
            transition: .15s;
        }

        .sidebar a:hover{
            background: #f1f3f5;
            color: var(--text);
        }

        .sidebar a.active{
            background: rgba(13,110,253,.12);
            color: var(--active);
            font-weight: 700;
        }

        /* Content */
        .content{
            padding: 20px;
        }

        .content-header{
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }

        /* Box/Card */
        .box{
            background: var(--card);
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            padding: 18px;
            margin-bottom: 16px;
        }

        /* Form */
        .form-control, .form-select{
            border-radius: 8px !important;
        }

        /* Responsive */
        @media (max-width: 992px){
            body{ padding-left: 0; }
            .sidebar{
                position: relative;
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--border);
            }
        }
    </style>

    {{-- ✅ PENTING: yield css harus DI LUAR <style> --}}
    @yield('css')
</head>

<body>

    <div class="sidebar d-flex flex-column">
        <h4 class="mb-3 text-center">
            <i class="fas fa-user-circle"></i>
            {{ Auth::user()->pengerajin->nama_pengerajin ?? Auth::user()->username }}
        </h4>
        <hr>

        <nav class="flex-grow-1">
            <a href="{{ route('pengerajin.profile') }}" class="{{ Route::is('pengerajin.profile') ? 'active' : '' }}">
                <i class="fas fa-user-edit"></i> Profil
            </a>
            <a href="{{ route('pengerajin.produk') }}" class="{{ Route::is('pengerajin.produk') ? 'active' : '' }}">
                <i class="fas fa-box-open"></i> Produk Anda
            </a>
            <a href="{{ route('pengerajin.produk-all') }}" class="{{ Route::is('pengerajin.produk-all') ? 'active' : '' }}">
                <i class="fas fa-globe"></i> Produk Semua
            </a>

            <h6 class="mt-3">LAPORAN USAHA</h6>

            <a href="{{ route('pengerajin.laporan_usaha.index') }}" class="{{ Route::is('pengerajin.laporan_usaha.index') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Dashboard Laporan
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.transaksi') }}" class="{{ Route::is('pengerajin.laporan_usaha.transaksi') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i> Semua Transaksi
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.pendapatan-usaha') }}" class="{{ Route::is('pengerajin.laporan_usaha.pendapatan-usaha') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i> Pendapatan Per Usaha
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-terlaris') }}" class="{{ Route::is('pengerajin.laporan_usaha.produk-terlaris') ? 'active' : '' }}">
                <i class="fas fa-fire"></i> Produk Terlaris
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-slow-moving') }}" class="{{ Route::is('pengerajin.laporan_usaha.produk-slow-moving') ? 'active' : '' }}">
                <i class="fas fa-hourglass-half"></i> Produk Slow Moving
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.kategori-produk') }}" class="{{ Route::is('pengerajin.laporan_usaha.kategori-produk') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Kategori Produk
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-favorite') }}" class="{{ Route::is('pengerajin.laporan_usaha.produk-favorite') ? 'active' : '' }}">
                <i class="fas fa-heart"></i> Produk Favorite
            </a>
            <a href="{{ route('pengerajin.laporan_usaha.produk-views') }}" class="{{ Route::is('pengerajin.laporan_usaha.produk-views') ? 'active' : '' }}">
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
        @hasSection('content_header')
            <div class="content-header">
                @yield('content_header')
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('js')
</body>
</html>
