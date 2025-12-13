{{-- resources/views/admin/usaha-edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit Data Usaha')

@section('content_header')
    <h1>Edit Data Usaha</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.usaha-update', $usaha->id) }}" method="POST" id="editUsahaForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- Kode Usaha -->
            <div class="form-group mb-3">
                <label for="kode_usaha">Kode Usaha</label>
                <input type="text" class="form-control" id="kode_usaha" name="kode_usaha"
                    value="{{ old('kode_usaha', $usaha->kode_usaha) }}" required>
            </div>

            <!-- Nama usaha -->
            <div class="form-group mb-3">
                <label for="nama_usaha">Nama usaha</label>
                <input type="text" class="form-control" id="nama_usaha" name="nama_usaha"
                    value="{{ old('nama_usaha', $usaha->nama_usaha) }}" required>
            </div>

            <!-- No Telepon -->
            <div class="form-group mb-3">
                <label for="telp_usaha">No Telepon</label>
                <input type="text" class="form-control" id="telp_usaha" name="telp_usaha"
                    value="{{ old('telp_usaha', $usaha->telp_usaha) }}" required>
                <small class="form-text text-muted">Contoh: 08123456789</small>
            </div>

            <!-- Email -->
            <div class="form-group mb-3">
                <label for="email_usaha">Email</label>
                <input type="email" class="form-control" id="email_usaha" name="email_usaha"
                    value="{{ old('email_usaha', $usaha->email_usaha) }}" required>
                <small class="form-text text-muted">Contoh : xxxgmail.com</small>
            </div>

            <!-- Deskripsi Usaha -->
            <div class="form-group mb-3">
                <label for="deskripsi_usaha">Deskripsi Usaha</label>
                <textarea class="form-control" id="deskripsi_usaha" name="deskripsi_usaha" rows="3" required>{{ old('deskripsi_usaha', $usaha->deskripsi_usaha) }}</textarea>
            </div>

            <!-- Foto Usaha -->
            <div class="form-group mb-3">
                <label for="foto_usaha">Foto Usaha</label>
                <input type="file" class="form-control" id="foto_usaha" name="foto_usaha" accept="image/*">
                <small class="form-text text-muted">Format yang didukung: JPG, JPEG, PNG, GIF</small>
                <small class="form-text text-muted">Ukuran maksimal: 2MB</small>
                @if ($usaha->foto_usaha)
                    <img src="{{ asset('storage/' . $usaha->foto_usaha) }}" alt="Foto Usaha" class="img-thumbnail mt-2"
                        style="max-width: 200px;">
                @endif
            </div>

            <!-- Link Gmap -->
            <div class="form-group mb-3">
                <label for="link_gmap_usaha">Link Gmap</label>
                <input type="text" class="form-control" id="link_gmap_usaha" name="link_gmap_usaha"
                    value="{{ old('link_gmap_usaha', $usaha->link_gmap_usaha) }}" required>
                <small class="form-text text-muted">Contoh: https://www.google.com/maps/place/...</small>
            </div>

            <!-- Status Usaha -->
            <div class="form-group mb-3">
                <label for="status_usaha">Status Usaha</label>
                <select class="form-select" id="status_usaha" name="status_usaha" required>
                    <option value="">Pilih Status Usaha</option>
                    <option value="aktif" {{ $usaha->status_usaha == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="tidak_aktif" {{ $usaha->status_usaha == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif
                    </option>
                    <option value="tutup" {{ $usaha->status_usaha == 'tutup' ? 'selected' : '' }}>Tutup</option>
                    <option value="pending" {{ $usaha->status_usaha == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="dibekukan" {{ $usaha->status_usaha == 'dibekukan' ? 'selected' : '' }}>Dibekukan
                    </option>
                </select>
            </div>


            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Update Data</button>
            <a href="{{ route('admin.usaha-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('editUsahaForm').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama_usaha').value.trim();
            if (!nama) {
                alert('Nama usaha tidak boleh kosong!');
                e.preventDefault();
            }
        });
        console.log("Form Edit Usaha loaded");
    </script>
@stop
