{{-- resources/views/admin/produk-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data produk')

@section('content_header')
    <h1>Edit Data produk</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.produk-update', $produk->id) }}" method="POST" id="editProdukForm"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- Kode produk -->
            <div class="form-group mb-3">
                <label for="kode_produk">Kode produk</label>
                <input type="text" class="form-control" id="kode_produk" name="kode_produk"
                    value="{{ old('kode_produk', $produk->kode_produk) }}" required>
            </div>

            <!-- Nama kategori produk -->
            <div class="form-group mb-3">
                <label for="kategori_produk_id">Nama kategori produk</label>
                <select class="form-control" id="kategori_produk_id" name="kategori_produk_id" required>
                    <option value="">Pilih kategori produk</option>
                    @foreach ($kategoriProduks as $kategoriProduk)
                        <option value="{{ $kategoriProduk->id }}"
                            {{ $produk->kategori_produk_id == $kategoriProduk->id ? 'selected' : '' }}>
                            {{ $kategoriProduk->nama_kategori_produk }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nama produk -->
            <div class="form-group mb-3">
                <label for="nama_produk">Nama produk</label>
                <input type="text" class="form-control" id="nama_produk" name="nama_produk"
                    value="{{ old('nama_produk', $produk->nama_produk) }}" required>
            </div>

            <!-- Deskripsi -->
            <div class="form-group mb-3">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required>
                    {{ old('deskripsi', $produk->deskripsi) }}</textarea>
            </div>

            <!-- Harga -->
            <div class="form-group mb-3">
                <label for="harga">Harga</label>
                <input type="number" class="form-control" id="harga" name="harga"
                    value="{{ old('harga', $produk->harga) }}" required>
            </div>

            <!-- Stok -->
            <div class="form-group mb-3">
                <label for="stok">Stok</label>
                <input type="number" class="form-control" id="stok" name="stok"
                    value="{{ old('stok', $produk->stok) }}" required>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.produk-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('editProdukForm').addEventListener('submit', function(e) {
            const kode = document.getElementById('kode_produk').value.trim();
            if (!kode) {
                alert('Kode produk tidak boleh kosong!');
                e.preventDefault();
            }

            const nama = document.getElementById('nama_produk').value.trim();
            if (!nama) {
                alert('Nama produk tidak boleh kosong!');
                e.preventDefault();
            }

            const deskripsi = document.getElementById('deskripsi').value.trim();
            if (!deskripsi) {
                alert('Deskripsi tidak boleh kosong!');
                e.preventDefault();
            }

            const harga = document.getElementById('harga').value.trim();
            if (!harga) {
                alert('Harga tidak boleh kosong!');
                e.preventDefault();
            }

            const stok = document.getElementById('stok').value.trim();
            if (!stok) {
                alert('Stok tidak boleh kosong!');
                e.preventDefault();
            }
        });
        console.log("Form Edit Produk loaded");
    </script>
@stop
