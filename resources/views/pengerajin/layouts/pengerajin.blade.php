<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Dashboard Pengerajin' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f5f5f5; }
        .sidebar {
            width: 230px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #343a40;
            color: white;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            margin-bottom: 6px;
            border-radius: 6px;
            text-decoration: none;
        }
        .sidebar a:hover { background: #495057; }

        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .container-box {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .content-wrapper {
            background: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 1000px;      /* batasi lebar agar tidak full kanan */
            margin: 0 auto;         /* supaya center */
        }

        .product-card {
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            transition: 0.2s;
        }

        .product-card:hover {
            transform: scale(1.02);
        }

        .product-img {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }

    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4>👤 {{ Auth::user()->pengerajin->nama_pengerajin ?? Auth::user()->username }}</h4>
        <hr>
        <a href="{{ route('pengerajin.profile') }}">👥 Profil</a>
        <a href="{{ route('pengerajin.produk') }}">📦 Produk Anda</a>
        <a href="{{ route('pengerajin.produk-all') }}">🌍 Produk Semua</a>

        <form action="{{ route('logout') }}" method="POST" class="mt-3">
            @csrf
            <button class="btn btn-danger w-100">Logout</button>
        </form>
    </div>

    <!-- Isi Halaman -->
    <div class="content">
        @yield('content')
    </div>

</body>
</html>
