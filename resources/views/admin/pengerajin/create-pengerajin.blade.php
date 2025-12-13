@extends('adminlte::page')

@section('title', 'Create Data Pengrajin')

@section('content_header')
    <h1>Create Data Pengrajin</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.pengerajin-store') }}" method="POST" id="createPengerajinForm">
            @csrf
            <!-- Kode Pengerajin -->
            <div class="form-group">
                <label for="kode_pengerajin">Kode Pengerajin</label>
                <input type="text" class="form-control" id="kode_pengerajin" name="kode_pengerajin"
                    placeholder="Masukkan Kode Pengerajin" required>
            </div>

            <!-- Nama Pengerajin -->
            <div class="mb-3">
                <label for="nama_pengerajin" class="form-label">Nama Pengerajin</label>
                <input type="text" class="form-control" id="nama_pengerajin" name="nama_pengerajin"
                    placeholder="Masukkan Nama Pengerajin" required>
            </div>
            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                    placeholder="Masukkan Username" required>
            </div>

            <!-- Jenis Kelamin -->
            <div class="mb-3">
                <label class="form-label d-block">Jenis Kelamin</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jk_pengerajin" id="jk_pria" value="P"
                        required>
                    <label class="form-check-label" for="jk_pria">Pria</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jk_pengerajin" id="jk_wanita" value="W"
                        required>
                    <label class="form-check-label" for="jk_wanita">Wanita</label>
                </div>
            </div>

            <!-- Usia -->
            <div class="mb-3">
                <label for="usia_pengerajin" class="form-label">Usia</label>
                <input type="number" class="form-control" id="usia_pengerajin" name="usia_pengerajin"
                    placeholder="Masukkan Usia Pengerajin" required>
            </div>

            <!-- No Telepon -->
            <div class="mb-3">
                <label for="telp_pengerajin" class="form-label">No Telepon</label>
                <input type="text" class="form-control" id="telp_pengerajin" name="telp_pengerajin"
                    placeholder="Masukkan No Telepon Pengerajin" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email_pengerajin" class="form-label">Email</label>
                <input type="email" class="form-control" id="email_pengerajin" name="email_pengerajin"
                    placeholder="Masukkan Email Pengerajin" required>
            </div>

            <!-- Alamat -->
            <div class="mb-3">
                <label for="alamat_pengerajin" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat_pengerajin" name="alamat_pengerajin" rows="3"
                    placeholder="Masukkan Alamat Pengerajin" required></textarea>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="{{ route('admin.pengerajin-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('createPengerajinForm').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama_pengerajin').value.trim();
            if (!nama) {
                alert('Nama Pengerajin tidak boleh kosong!');
                e.preventDefault();
                return false;
            }
            const usia = document.getElementById('usia_pengerajin').value;
            if (usia < 0) {
                alert('Usia tidak boleh negatif!');
                e.preventDefault();
                return false;
            }
            const telp = document.getElementById('telp_pengerajin').value.trim();
            if (!telp) {
                alert('No Telepon tidak boleh kosong!');
                e.preventDefault();
                return false;
            }
            if (telp.length < 10) {
                alert('No Telepon harus lebih dari 10 digit!');
                e.preventDefault();
                return false;
            }
            const email = document.getElementById('email_pengerajin').value.trim();
            if (!email) {
                alert('Email tidak boleh kosong!');
                e.preventDefault();
                return false;
            }
            const alamat = document.getElementById('alamat_pengerajin').value.trim();
            if (!alamat) {
                alert('Alamat tidak boleh kosong!');
                e.preventDefault();
                return false;
            }
        });

        console.log("Form Create Pengerajin loaded");
    </script>
@stop
