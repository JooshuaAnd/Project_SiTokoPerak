@extends('adminlte::page')

@section('title', 'Create Data Usaha - Jenis Usaha')

@section('content_header')
    <h1>Create Data Usaha - Jenis Usaha</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.usaha_jenis-store') }}" method="POST" id="createUsahaJenisForm">
            @csrf
            <!-- Nama Usaha -->
            <div class="form-group">
                <label for="usaha_id">Nama Usaha</label>
                <select class="form-control" id="usaha_id" name="usaha_id" required>
                    <option value="">Pilih Usaha</option>
                    @foreach ($usahas as $usaha)
                        <option value="{{ $usaha->id }}">{{ $usaha->nama_usaha }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Nama jenis_usaha -->
            <div class="form-group">
                <label for="jenis_usaha_id">Kode Jenis Usaha</label>
                <select class="form-control" id="jenis_usaha_id" name="jenis_usaha_id" required>
                    <option value="">Pilih Kode jenis_usaha</option>
                    @foreach ($jenisUsahas as $jenis_usaha)
                        <option value="{{ $jenis_usaha->id }}">{{ $jenis_usaha->nama_jenis_usaha }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="{{ route('admin.usaha_jenis-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('createUsahaJenisForm">').addEventListener('submit', function(e) {
            // Contoh validasi dasar: memastikan jenis_usaha tidak kosong
            if (document.getElementById('jenis_usaha_id').value.trim() === '') {
                alert('Kode jenis_usaha wajib dipilih!');
                e.preventDefault();
                return false;
            }

            // Contoh validasi dasar: memastikan usaha_id tidak kosong
            if (document.getElementById('usaha_id').value.trim() === '') {
                alert('Usaha wajib dipilih!');
                e.preventDefault();
                return false;
            }

            // usaha_id dan jenis_usaha_id tidak boleh sama dengan data sebelumnya
            var usahaId = parseInt(document.getElementById('usaha_id').value);
            var jenisUsahaId = parseInt(document.getElementById('jenis_usaha_id').value);
            var existingUsahaId = {{ $existingUsahaId ?? 'null' }};
            var existingJenisUsahaId = {{ $existingJenisUsahaId ?? 'null' }};

            if (usahaId === existingUsahaId && jenisUsahaId === existingJenisUsahaId) {
                alert('Data sudah ada sebelumnya!');
                e.preventDefault();
                return false;
            }

            // Jika semua validasi lulus, form akan disubmit
            alert('Form berhasil disubmit!');
        });

        console.log("Form Create Pengerajin loaded");
    </script>
@stop
