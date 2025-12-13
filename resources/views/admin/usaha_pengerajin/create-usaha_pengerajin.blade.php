@extends('adminlte::page')

@section('title', 'Create Data Usaha Pengerajin')

@section('content_header')
    <h1>Create Data Usaha - Pengerajin</h1>
@stop

@section('content')
    <div class="container">
        <form action="{{ route('admin.usaha_pengerajin-store') }}" method="POST" id="createUsahaPengerajinForm">
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

            <!-- Nama Pengerajin -->
            <div class="form-group">
                <label for="pengerajin_id">Nama Pengerajin</label>
                <select class="form-control" id="pengerajin_id" name="pengerajin_id" required>
                    <option value="">Pilih Kode Pengerajin</option>
                    @foreach ($pengerajins as $pengerajin)
                        <option value="{{ $pengerajin->id }}">{{ $pengerajin->nama_pengerajin }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="{{ route('admin.usaha_pengerajin-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('createUsahaPengerajinForm').addEventListener('submit', function(e) {
            // Contoh validasi dasar: memastikan Pengerajin tidak kosong
            if (document.getElementById('pengerajin_id').value.trim() === '') {
                alert('Kode Pengerajin wajib dipilih!');
                e.preventDefault();
                return false;
            }

            // Contoh validasi dasar: memastikan kategori usaha_id tidak kosong
            if (document.getElementById('usaha_id').value.trim() === '') {
                alert('Usaha wajib dipilih!');
                e.preventDefault();
                return false;
            }

            //usaha_id dan pengerajin_id tidak boleh sama dengan data sebelumnya
            var usahaId = parseInt(document.getElementById('usaha_id').value);
            var jenisUsahaId = parseInt(document.getElementById('pengerajin_id').value);
            var existingUsahaId = {{ $existingUsahaId ?? 'null' }};
            var existingPengerajinId = {{ $existingPengerajinId ?? 'null' }};

            if (usahaId === existingUsahaId && jenisUsahaId === existingPengerajinId) {
                alert('Data sudah ada sebelumnya!');
                e.preventDefault();
                return false;
            }

        });

        console.log("Form Create Pengerajin loaded");
    </script>
@stop
