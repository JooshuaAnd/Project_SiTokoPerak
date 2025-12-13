@extends('adminlte::page')

@section('title', 'Create Data Kategori Produk')

@section('content_header')
    <h1>Create Data Kategori Produk</h1>
@stop

@section('content')
<div class="container">
    <form action="{{ route('admin.kategori_produk-store') }}" method="POST" id="createKategoriProdukForm">
        @csrf
        <!-- Kode Kategori Produk -->
        <div class="mb-3">
            <label for="kode_kategori_produk" class="form-label">Kode Kategori Produk</label>
            <input type="text" class="form-control" id="kode_kategori_produk" name="kode_kategori_produk" placeholder="Masukkan Kode Kategori Produk" required>
        </div>

        <!-- Nama Kategori Produk -->
        <div class="mb-3">
            <label for="nama_kategori_produk" class="form-label">Nama Kategori Produk</label>
            <input type="text" class="form-control" id="nama_kategori_produk" name="nama_kategori_produk" placeholder="Masukkan Nama Produk" required>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary">Simpan Data</button>
        <a href="{{ route('admin.kategori_produk-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('createKategoriProdukForm').addEventListener('submit', function(e) {
            // Contoh validasi dasar: memastikan kode tidak kosong
            if (document.getElementById('kode_kategori_produk').value.trim() === '') {
                alert('Kode Kategori Produk wajib diisi!');
                e.preventDefault();
                return false;
            }

            // Contoh validasi dasar: memastikan nama tidak kosong
            const nama = document.getElementById('nama_kategori_produk').value.trim();
            if (nama === '') {
                alert('Nama Produk wajib diisi!');
                e.preventDefault();
                return false;
            }
            // Validasi lainnya bisa ditambahkan di sini jika diperlukan
        });

        console.log("Form Create Pengerajin loaded");
    </script>
@stop
