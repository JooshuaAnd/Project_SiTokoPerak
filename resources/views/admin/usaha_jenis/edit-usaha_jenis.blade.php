{{-- resources/views/admin/produk-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data Usaha Jenis')

@section('content_header')
    <h1>Edit Data Usaha Jenis</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.usaha_jenis-update', $usahaJenis->id) }}" method="POST" id="editUsahaJenisForm">
            @csrf
            @method('PUT')
            <!-- Nama Usaha -->
            <div class="form-group mb-3">
                <label for="usaha_id">Nama Usaha</label>
                <select class="form-control" id="usaha_id" name="usaha_id" required>
                    <option value="">Pilih Usaha</option>
                    @foreach ($usahas as $usaha)
                        <option value="{{ $usaha->id }}" {{ $usahaJenis->usaha_id == $usaha->id ? 'selected' : '' }}>
                            {{ $usaha->nama_usaha }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Nama jenis_usaha -->
            <div class="form-group mb-3">
                <label for="jenis_usaha_id">Kode Jenis Usaha</label>
                <select class="form-control" id="jenis_usaha_id" name="jenis_usaha_id" required>
                    <option value="">Pilih Kode jenis_usaha</option>
                    @foreach ($jenisUsahas as $jenis_usaha)
                        <option value="{{ $jenis_usaha->id }}"
                            {{ $usahaJenis->jenis_usaha_id == $jenis_usaha->id ? 'selected' : '' }}>
                            {{ $jenis_usaha->nama_jenis_usaha }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.usaha_jenis-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('editUsahaJenisForm">').addEventListener('submit', function(e) {
            // Contoh validasi dasar: memastikan usaha tidak kosong
            if (document.getElementById('usaha_id').value.trim() === '') {
                alert('Nama Usaha wajib dipilih!');
                e.preventDefault();
                return false;
            }

            // Contoh validasi dasar: memastikan jenis usaha tidak kosong
            if (document.getElementById('jenis_usaha_id').value.trim() === '') {
                alert('Kode Jenis Usaha wajib dipilih!');
                e.preventDefault();
                return false;
            }

        });
        console.log("Form Edit Daftar Produk loaded");
    </script>
@stop
