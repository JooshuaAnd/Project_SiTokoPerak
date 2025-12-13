{{-- resources/views/admin/produk-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data Kategori Produk')

@section('content_header')
    <h1>Edit Data Kategori Produk</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.kategori_produk-update', $kategoriProduk->id) }}" method="POST" id="editKategoriProdukForm">
            @csrf
            @method('PUT')
            <!-- Kode Kategori Produk -->
            <div class="form-group mb-3">
                <label for="kode_kategori_produk">Kode Kategori Produk</label>
                <input type="text" class="form-control" id="kode_kategori_produk" name="kode_kategori_produk"
                    value="{{ old('kode_kategori_produk', $kategoriProduk->kode_kategori_produk) }}" required>
            </div>

            <!-- Nama Kategori Produk -->
            <div class="form-group mb-3">
                <label for="nama_kategori_produk">Nama Kategori Produk</label>
                <input type="text" class="form-control" id="nama_kategori_produk" name="nama_kategori_produk"
                    value="{{ old('nama_kategori_produk', $kategoriProduk->nama_kategori_produk) }}" required>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.kategori_produk-index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@stop

@section('css')
    <!-- Custom CSS jika diperlukan -->
    <link rel="stylesheet" href="/css/custom.css">
@stop

@section('js')
    <script>
        // Contoh validasi sederhana sebelum submit
        document.getElementById('editKategoriProdukForm').addEventListener('submit', function(e) {
            const kode = document.getElementById('kode_kategori_produk').value.trim();
            if (!kode) {
                alert('Kode Kategori Produk tidak boleh kosong!');
                e.preventDefault();
            }

            const nama = document.getElementById('nama_kategori_produk').value.trim();
            if (!nama) {
                alert('Nama Kategori Produk tidak boleh kosong!');
                e.preventDefault();
            }
        });
        console.log("Form Edit Kategori Produk loaded");
    </script>
@stop
