{{-- resources/views/admin/produk-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data Usaha Pengerajin')

@section('content_header')
    <h1>Edit Data Usaha - Pengerajin</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.usaha_pengerajin-update', $usahaPengerajin->id) }}" method="POST"
            id="editUsahaPengerajinForm">
            @csrf
            @method('PUT')
            <!-- Nama Usaha -->
            <div class="form-group mb-3">
                <label for="usaha_id">Nama Usaha</label>
                <select class="form-control" id="usaha_id" name="usaha_id" required>
                    <option value="">Pilih Usaha</option>
                    @foreach ($usahas as $usaha)
                        <option value="{{ $usaha->id }}"
                            {{ $usahaPengerajin->usaha_id == $usaha->id ? 'selected' : '' }}>
                            {{ $usaha->nama_usaha }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Nama Pengerajin -->
            <div class="form-group mb-3">
                <label for="pengerajin_id">Nama Pengerajin</label>
                <select class="form-control" id="pengerajin_id" name="pengerajin_id" required>
                    <option value="">Pilih Pengerajin</option>
                    @foreach ($pengerajins as $pengerajin)
                        <option value="{{ $pengerajin->id }}"
                            {{ $usahaPengerajin->pengerajin_id == $pengerajin->id ? 'selected' : '' }}>
                            {{ $pengerajin->nama_pengerajin }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.usaha_pengerajin-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('editUsahaPengerajinForm').addEventListener('submit', function(e) {
            const usahaId = document.getElementById('usaha_id').value.trim();
            const pengerajinId = document.getElementById('pengerajin_id').value.trim();
            if (!usahaId) {
                alert('Usaha tidak boleh kosong!');
                e.preventDefault();
            }
            if (!pengerajinId) {
                alert('Pengerajin tidak boleh kosong!');
                e.preventDefault();
            } 
        });
        console.log("Form Edit Usaha Pengerajin loaded");
    </script>
@stop
