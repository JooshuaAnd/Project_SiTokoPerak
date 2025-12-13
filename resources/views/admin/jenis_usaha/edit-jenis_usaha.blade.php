{{-- resources/views/admin/usaha-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data Jenis Usaha')

@section('content_header')
    <h1>Edit Data Jenis Usaha</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.jenis_usaha-update', $jenisUsaha->id) }}" method="POST" id="editJenisUsahaForm">
            @csrf
            @method('PUT')
            <!-- Kode Jenis Usaha -->
            <div class="form-group mb-3">
                <label for="kode_jenis_usaha">Kode Jenis Usaha</label>
                <input type="text" class="form-control" id="kode_jenis_usaha" name="kode_jenis_usaha"
                    value="{{ old('kode_jenis_usaha', $jenisUsaha->kode_jenis_usaha) }}" required>
            </div>

            <!-- Nama Jenis Usaha -->
            <div class="form-group mb-3">
                <label for="nama_jenis_usaha">Nama Jenis Usaha</label>
                <input type="text" class="form-control" id="nama_jenis_usaha" name="nama_jenis_usaha"
                    value="{{ old('nama_usaha', $jenisUsaha->nama_jenis_usaha) }}" required>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.jenis_usaha-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('editJenisUsahaForm').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama_jenis_usaha').value.trim();
            if (!nama) {
                alert('Nama usaha tidak boleh kosong!');
                e.preventDefault();
            }
        });
        console.log("Form Edit Usaha loaded");
    </script>
@stop
