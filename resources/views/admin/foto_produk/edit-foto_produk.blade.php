{{-- resources/views/admin/produk-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data Foto Produk')

@section('content_header')
    <h1>Edit Data Foto Produk</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.foto_produk-update', $fotoProduk->id) }}" method="POST" id="editFotoProdukForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- Kode Foto Produk -->
            <div class="form-group mb-3">
                <label for="kode_foto_produk">Kode Foto Produk</label>
                <input type="text" class="form-control" id="kode_foto_produk" name="kode_foto_produk"
                    value="{{ old('kode_foto_produk', $fotoProduk->kode_foto_produk) }}" required>
            </div>

            <!-- Kode Produk -->
            <div class="form-group mb-3">
                <label for="produk_id">Nama Produk</label>
                <select class="form-control" id="produk_id" name="produk_id" required>
                    <option value="">Pilih Nama Produk</option>
                    @foreach ($produks as $produk)
                        <option value="{{ $produk->id }}" {{ $fotoProduk->produk_id == $produk->id ? 'selected' : '' }}>
                            {{ $produk->nama_produk }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Silakan pilih produk yang terkait dengan foto ini.</small>
                <small class="text-muted">Pastikan untuk memilih produk yang benar agar foto dapat terhubung dengan produk yang tepat.</small>
            </div>

            <!-- File Foto Produk -->
            <div class="form-group mb-3">
                <label for="foto_produk">File Foto Produk</label>
                <input type="file" class="form-control" id="file_foto_produk" name="file_foto_produk" accept="image/*">
                <small class="text-muted">Format yang didukung: JPG, PNG, GIF.</small>
                <small class="text-muted">Ukuran maksimum: 2MB.</small>
                <small class="text-muted">Silakan pilih file foto produk yang ingin diunggah.</small>
                <small class="text-muted">Jika tidak ada perubahan, biarkan kosong.</small>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.foto_produk-index') }}" class="btn btn-secondary">Batal</a>
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
