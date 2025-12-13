@extends('adminlte::page')

@section('title', 'Create Data Pengerajin')

@section('content_header')
    <h1>Create Data Usaha</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.usaha-store') }}" method="POST" id="createUsahaForm" enctype="multipart/form-data">
            @csrf
            <!-- Kode Usaha -->
            <div class="form-group">
                <label for="kode_usaha">Kode Usaha</label>
                <input type="text" class="form-control" id="kode_usaha" name="kode_usaha" placeholder="Masukkan Kode Usaha"
                    required>
            </div>

            <!-- Nama Usaha -->
            <div class="form-group mb-3">
                <label for="nama_usaha">Nama Usaha</label>
                <input type="text" class="form-control" id="nama_usaha" name="nama_usaha"
                    placeholder="Masukkan Nama Usaha" required>
            </div>

            <!-- Telepon Usaha -->
            <div class="form-group mb-3">
                <label for="telp_usaha">No Telepon</label>
                <input type="text" class="form-control" id="telp_usaha" name="telp_usaha"
                    placeholder="Masukkan No Telepon" required>
                <small class="form-text text-muted">Contoh: 08123456789</small>
            </div>

            <!-- Email Usaha -->
            <div class="form-group mb-3">
                <label for="email_usaha">Email</label>
                <input type="email" class="form-control" id="email_usaha" name="email_usaha"
                    placeholder="Masukkan Email Usaha" required>
                <small class="form-text text-muted">Contoh : xxx@gmail.com</small>
            </div>

            <!-- Deskripsi Usaha -->
            <div>
                <label for="deskripsi_usaha">Deskripsi Usaha</label>
                <textarea class="form-control" id="deskripsi_usaha" name="deskripsi_usaha" rows="3"
                    placeholder="Masukkan Deskripsi Usaha" required></textarea>
            </div>

            <!-- Foto Usaha -->
            <div class="form-group mb-3">
                <label for="foto_usaha">Foto Usaha</label>
                <input type="file" class="form-control" id="foto_usaha" name="foto_usaha" accept="image/*">
                <small class="form-text text-muted">Format yang didukung: JPG, JPEG, PNG, GIF</small>
                <small class="form-text text-muted">Ukuran maksimal: 2MB</small>
            </div>

            <!-- Link Gmap -->
            <div class="form-group mb-3">
                <label for="link_gmap_usaha">Link Gmap</label>
                <input type="text" class="form-control" id="link_gmap_usaha" name="link_gmap_usaha"
                    placeholder="Masukkan Link Gmap" required>
                <small class="form-text text-muted">Contoh: https://goo.gl/maps/xyz123</small>
            </div>

            <!-- Status Usaha -->
            <div class="form-group mb-3">
                <label for="status_usaha">Status Usaha</label>
                <select class="form-select" id="status_usaha" name="status_usaha" required>
                    <option value="">Pilih Status Usaha</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                    <option value="tutup">Tutup</option>
                    <option value="pending">Pending</option>
                    <option value="dibekukan">Dibekukan</option>
                </select>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="{{ route('admin.usaha-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('createUsahaForm').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama_usaha').value.trim();
            if (!nama) {
                alert('Nama usaha tidak boleh kosong!');
                e.preventDefault();
            }
            const telp = document.getElementById('telp_usaha').value.trim();
            if (!telp) {
                alert('No Telepon tidak boleh kosong!');
                e.preventDefault();
            }
            const email = document.getElementById('email_usaha').value.trim();
            if (!email) {
                alert('Email tidak boleh kosong!');
                e.preventDefault();
            }
            const deskripsi = document.getElementById('deskripsi_usaha').value.trim();
            if (!deskripsi) {
                alert('Deskripsi tidak boleh kosong!');
                e.preventDefault();
            }
            const linkGmap = document.getElementById('link_gmap_usaha').value.trim();
            if (!linkGmap) {
                alert('Link Gmap tidak boleh kosong!');
                e.preventDefault();
            }
            const status = document.getElementById('status_usaha').value.trim();
            if (!status) {
                alert('Status usaha tidak boleh kosong!');
                e.preventDefault();
            }
            const foto = document.getElementById('foto_usaha').files[0];
            if (foto && !/\.(jpg|jpeg|png|gif)$/i.test(foto.name)) {
                alert('Format foto tidak valid! Hanya JPG, JPEG, PNG, GIF yang diperbolehkan.');
                e.preventDefault();
            }
        });

        console.log("Form Create Usaha loaded");
    </script>
@stop
