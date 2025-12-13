{{-- resources/views/admin/produk-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data Usaha Produk')

@section('content_header')
    <h1>Edit Data Usaha - Produk</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.usaha_produk-update', $usahaProduk->id) }}" method="POST" id="editUsahaProdukForm">
            @csrf
            @method('PUT')
            <!-- Nama Usaha -->
            <div class="form-group mb-3">
                <label for="usaha_id">Nama Usaha</label>
                <select class="form-control" id="usaha_id" name="usaha_id" required>
                    <option value="">Pilih Usaha</option>
                    @foreach ($usahas as $usaha)
                        <option value="{{ $usaha->id }}" {{ $usahaProduk->usaha_id == $usaha->id ? 'selected' : '' }}>
                            {{ $usaha->nama_usaha }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Nama produk -->
            <div class="form-group mb-3">
                <label for="produk_id">Nama Produk</label>
                <select class="form-control" id="produk_id" name="produk_id" required>
                    <option value="">Pilih Nama produk</option>
                    @foreach ($produks as $produk)
                        <option value="{{ $produk->id }}" {{ $usahaProduk->produk_id == $produk->id ? 'selected' : '' }}>
                            {{ $produk->nama_produk }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.usaha_produk-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('editUsahaProdukForm').addEventListener('submit', function(e) {
            const usahaId = document.getElementById('usaha_id').value.trim();
            const produkId = document.getElementById('produk_id').value.trim();
            if (!usahaId) {
                alert('Nama Usaha wajib dipilih!');
                e.preventDefault();
                return false;
            }
            if (!produkId) {
                alert('Nama Produk wajib dipilih!');
                e.preventDefault();
                return false;
            }
        });
        console.log("Form Edit Daftar Produk loaded");
    </script>
@stop
