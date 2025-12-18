@extends('adminlte::page')

@section('title', 'Produk')

@section('css')
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
@stop
@section('content_header')
    <h1 style="color:black; font-weight:600;">Data Produk</h1>
@stop
@section('content')
    <a href="{{ route('admin.produk-create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-add"></i> Tambah Produk</a>
    {{-- tambahkan jarak dan garis --}}
    <br>
    <hr color="#ccc"> {{-- tambahkan garis lurus --}}

    <div class="row">
        @forelse ($produks as $produk)
            <div class="col-md-4 col-lg-3"> {{-- Menggunakan grid untuk tampilan card --}}
                <div class="product-card">
                    @if($produk->foto_produk)
                        <img src="{{ asset('storage/' . $produk->foto_produk) }}" alt="{{ $produk->nama_produk }}">
                    @else
                        <img src="{{ asset('images/default-product.png') }}" alt="No Image"> {{-- Default image --}}
                    @endif
                    <h5>{{ $produk->nama_produk }}</h5>
                    <p><strong>Kode:</strong> {{ $produk->kode_produk }}</p>
                    <p><strong>Pengerajin:</strong> {{ $produk->pengerajin->nama_pengerajin }}</p>
                    <p><strong>Kategori:</strong> {{ $produk->kategoriProduk->nama_kategori_produk }}</p>
                    <p><strong>Harga:</strong> Rp {{ number_format($produk->harga, 2, ',', '.') }}</p>
                    <p><strong>Stok:</strong> {{ $produk->stok }}</p>
                    <p><strong>Deskripsi:</strong> {{ Str::limit($produk->deskripsi, 100) }}</p> {{-- Batasi deskripsi --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.produk-edit', $produk->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.produk-destroy', $produk->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Produk"
                                onclick="return confirm('Anda yakin ingin menghapus produk ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center">Tidak ada produk yang tersedia.</p>
            </div>
        @endforelse
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
@stop
@stop
@section('js')
    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        // DataTables is no longer needed for card view, but keeping the JS for logout functionality
        // If you want to re-introduce DataTables for a list view, you can uncomment and adapt this.
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const logoutBtn = document.getElementById('logout-button');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('logout-form').submit();
                });
            }
        });
    </script>
@stop
