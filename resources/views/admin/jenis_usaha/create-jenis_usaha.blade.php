@extends('adminlte::page')

@section('title', 'Create Data Jenis Usaha')

@section('content_header')
    <h1>Create Data Jenis Usaha</h1>
@stop

@section('content')
<div class="container">
    <form action="{{ route('admin.jenis_usaha-store') }}" method="POST" id="createJenisUsahaForm">
        @csrf
        <!-- Jenis Usaha-->
        <div class="mb-3">
            <label for="kode_jenis_usaha" class="form-label">Kode Jenis Usaha</label>
            <input type="kode_jenis_usaha" class="form-control" id="kode_jenis_usaha" name="kode_jenis_usaha" placeholder="Masukkan Kode Jenis Usaha" required>
        </div>

        <!-- Nama usaha -->
        <div class="mb-3">
            <label for="nama_jenis_usaha" class="form-label">Nama Jenis Usaha</label>
            <input type="text" class="form-control" id="nama_jenis_usaha" name="nama_jenis_usaha" placeholder="Masukkan Nama Usaha" required>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary">Simpan Data</button>
        <a href="{{ route('admin.jenis_usaha-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('createJenisUsahaForm">').addEventListener('submit', function(e) {
            // Contoh validasi dasar: memastikan kode tidak kosong
            if (document.getElementById('kode_jenis_usaha').value.trim() === '') {
                alert('Kode Jenis Usaha wajib diisi!');
                e.preventDefault();
                return false;
            }

            // Contoh validasi dasar: memastikan nama tidak kosong
            const nama = document.getElementById('nama_jenis_usaha').value.trim();
            if (nama === '') {
                alert('Nama Jenis Usaha wajib diisi!');
                e.preventDefault();
                return false;
            }
            // Validasi lainnya bisa ditambahkan di sini jika diperlukan
        });

        console.log("Form Create Jenis Usaha loaded");
    </script>
@stop
