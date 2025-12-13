@extends('adminlte::page')

@section('title', 'Create Data Produk')

@section('content_header')
    <h1>Create Data Produk</h1>
@stop

@section('content')
<div class="container">
    <form action="{{ route('admin.produk-store') }}" method="POST" id="createProdukForm" enctype="multipart/form-data">
        @csrf
        <!-- Kode Produk -->
        <div class="form-group">
            <label for="kode_produk">Kode Produk</label>
            <input type="text" class="form-control" id="kode_produk" name="kode_produk" placeholder="Masukkan Kode Produk" required>
        </div>

        <!-- Nama Produk -->
        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" class="form-control" id="nama_produk" name="nama_produk" placeholder="Masukkan Nama Produk" required>
        </div>

        <!-- Nama Kategori Produk -->
        <div class="mb-3">
            <label for="kategori_produk_id" class="form-label">Nama Kategori Produk</label>
            <select class="form-control" id="kategori_produk_id" name="kategori_produk_id" required>
                <option value="">Pilih Kategori Produk</option>
                @foreach ($kategoriProduks as $kategoriProduk)
                    <option value="{{ $kategoriProduk->id }}">{{ $kategoriProduk->nama_kategori_produk }}</option>
                @endforeach
            </select>
        </div>
        <!-- Nama Pengrajin -->
        <div class="mb-3">
            <label for="pengerajin_produk_id" class="form-label">Nama Pengerajin Produk</label>
            <select class="form-control" id="pengerajin_produk_id" name="pengerajin_id" required>
                <option value="">Pilih Pengerajin Produk</option>
                @foreach ($pengerajins as $pengerajin)
                    <option value="{{ $pengerajin->id }}">{{ $pengerajin->nama_pengerajin }}</option>
                @endforeach
            </select>
        </div>

        <!-- Deskripsi -->
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Masukkan Deskripsi Produk" required></textarea>
        </div>

        <!-- Harga -->
        <div class="mb-3">
            <label for="harga" class="form-label">Harga</label>
            <input type="number" class="form-control" id="harga" name="harga" placeholder="Masukkan Harga Produk" required>
        </div>

        <!-- Stok -->
        <div class="mb-3">
            <label for="stok" class="form-label">Stok</label>
            <input type="number" class="form-control" id="stok" name="stok" placeholder="Masukkan Stok Produk" required>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary">Simpan Data</button>
        <a href="{{ route('admin.produk-index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@stop

@section('css')
    <!-- Custom CSS jika diperlukan -->
    <link rel="stylesheet" href="/css/custom.css">
@stop

@section('js')
    <!-- Validasi form sederhana dengan JavaScript -->
    <script>
        document.getElementById('createProdukForm').addEventListener('submit', function(e) {
            // Contoh validasi dasar: memastikan kode tidak kosong
            if (document.getElementById('kode_produk').value.trim() === '') {
                alert('Kode Produk wajib diisi!');
                e.preventDefault();
                return false;
            }

            // Contoh validasi dasar: memastikan nama tidak kosong
            const nama = document.getElementById('nama_produk').value.trim();
            if (nama === '') {
                alert('Nama Produk wajib diisi!');
                e.preventDefault();
                return false;
            }
            // Validasi lainnya bisa ditambahkan di sini jika diperlukan
        });

        console.log("Form Create Produk loaded");
    </script>
@stop
