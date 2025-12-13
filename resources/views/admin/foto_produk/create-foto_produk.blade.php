@extends('adminlte::page')

@section('title', 'Create Data Foto Produk')

@section('content_header')
    <h1>Create Data Foto Produk</h1>
@stop

@section('content')
<div class="container">
    <form action="{{ route('admin.foto_produk-store') }}" method="POST" id="createFotoProdukForm" enctype="multipart/form-data">
        @csrf
        <!-- Kode Foto Produk -->
        <div class="mb-3">
            <label for="kode_foto_produk" class="form-label">Kode Foto Produk</label>
            <input type="text" class="form-control" id="kode_foto_produk" name="kode_foto_produk" placeholder="Masukkan Kode Foto Produk" required>
        </div>

        <!-- Produk Id-->
        <div class="mb-3">
            <label for="produk_id" class="form-label">Nama Produk</label>
            <select class="form-control" id="produk_id" name="produk_id" required>
                <option value="">Pilih Nama Produk</option>
                @foreach ($produks as $produk)
                    <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                @endforeach
            </select>
            <small class="text-muted">Silakan pilih produk yang terkait dengan foto ini.</small>
            <small class="text-muted">Pastikan untuk memilih produk yang benar agar foto dapat terhubung dengan produk
                yang tepat.</small>
        </div>

        <!-- File Foto Produk -->
        <div class="mb-3">
            <label for="file_foto_produk" class="form-label">File Foto Produk</label>
            <input type="file" class="form-control" id="file_foto_produk" name="file_foto_produk" accept="image/*" required>
            <small class="text-muted">Format yang didukung: JPG, PNG, GIF.</small>
            <small class="text-muted">Ukuran maksimum: 2MB.</small>
            <small class="text-muted">Silakan pilih file foto produk yang ingin diunggah.</small>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary">Simpan Data</button>
        <a href="{{ route('admin.foto_produk-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('createFotoProdukForm').addEventListener('submit', function(e) {
            // Contoh validasi dasar: memastikan kode tidak kosong
            if (document.getElementById('kode_foto_produk').value.trim() === '') {
                alert('Kode Foto Produk wajib diisi!');
                e.preventDefault();
                return false;
            }
            // Contoh validasi dasar: memastikan kode produk tidak kosong
            if (document.getElementById('kode_produk').value.trim() === '') {
                alert('Kode Produk wajib dipilih!');
                e.preventDefault();
                return false;
            }
            // Contoh validasi dasar: memastikan file foto produk tidak kosong
            if (document.getElementById('nama_foto_produk').files.length === 0) {
                alert('File Foto Produk wajib diunggah!');
                e.preventDefault();
                return false;
            }
        });

        console.log("Form Create Pengerajin loaded");
    </script>
@stop
