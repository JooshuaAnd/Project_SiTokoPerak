{{-- resources/views/admin/pengerajin-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data Pengerajin')

@section('content_header')
    <h1>Edit Data Pengerajin</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.pengerajin-update', $pengerajin->id) }}" method="POST" id="editPengerajinForm">
            @csrf
            @method('PUT')
            <!-- Kode Pengerajin -->
            <div class="form-group">
                <label for="kode_pengerajin">Kode Pengerajin</label>
                <input type="text" class="form-control" id="kode_pengerajin" name="kode_pengerajin"
                    value="{{ old('kode_pengerajin', $pengerajin->kode_pengerajin) }}" required>
            </div>

            <!-- Nama Pengerajin -->
            <div class="form-group mb-3">
                <label for="nama_pengerajin">Nama Pengerajin</label>
                <input type="text" class="form-control" id="nama_pengerajin" name="nama_pengerajin"
                    value="{{ old('nama_pengerajin', $pengerajin->nama_pengerajin) }}" required>
            </div>

            <!-- Jenis Kelamin -->
            <div class="form-group mb-3">
                <label for="jk_pengerajin" class="form-label d-block">Jenis Kelamin</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jk_pengerajin" id="jk_pria" value="P"
                        {{ $pengerajin->jk_pengerajin == 'P' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="jk_pria">Pria</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jk_pengerajin" id="jk_wanita" value="W"
                        {{ $pengerajin->jk_pengerajin == 'W' ? 'checked' : '' }} required>
                    <label class="form-check-label" for="jk_wanita">Wanita</label>
                </div>
            </div>


            <!-- Usia -->
            <div class="form-group mb-3">
                <label for="usia_pengerajin">Usia</label>
                <input type="number" class="form-control" id="usia_pengerajin" name="usia_pengerajin"
                    value="{{ old('usia_pengerajin', $pengerajin->usia_pengerajin) }}" required>
            </div>

            <!-- No Telepon -->
            <div class="form-group mb-3">
                <label for="telp_pengerajin">No Telepon</label>
                <input type="text" class="form-control" id="telp_pengerajin" name="telp_pengerajin"
                    value="{{ old('telp_pengerajin', $pengerajin->telp_pengerajin) }}" required>
            </div>

            <!-- Email -->
            <div class="form-group mb-3">
                <label for="email_pengerajin">Email</label>
                <input type="email" class="form-control" id="email_pengerajin" name="email_pengerajin"
                    value="{{ old('email_pengerajin', $pengerajin->email_pengerajin) }}" required>
            </div>

            <!-- Alamat -->
            <div class="form-group mb-3">
                <label for="alamat_pengerajin">Alamat</label>
                <textarea class="form-control" id="alamat_pengerajin" name="alamat_pengerajin" rows="3" required>{{ old('alamat_pengerajin', $pengerajin->alamat_pengerajin) }}</textarea>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.pengerajin-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('editPengerajinForm').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama_pengerajin').value.trim();
            if (!nama) {
                alert('Nama Pengerajin tidak boleh kosong!');
                e.preventDefault();
            }
            const usia = document.getElementById('usia_pengerajin').value.trim();
            if (!usia || isNaN(usia) || usia <= 0) {
                alert('Usia Pengerajin tidak valid!');
                e.preventDefault();
            }
            const telp = document.getElementById('telp_pengerajin').value.trim();
            if (!telp || isNaN(telp) || telp.length < 10) {
                alert('No Telepon Pengerajin tidak valid!');
                e.preventDefault();
            }
            const email = document.getElementById('email_pengerajin').value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailPattern.test(email)) {
                alert('Email Pengerajin tidak valid!');
                e.preventDefault();
            }
            const alamat = document.getElementById('alamat_pengerajin').value.trim();
            if (!alamat) {
                alert('Alamat Pengerajin tidak boleh kosong!');
                e.preventDefault();
            }
        });
        console.log("Form Edit Pengerajin loaded");
    </script>
@stop
