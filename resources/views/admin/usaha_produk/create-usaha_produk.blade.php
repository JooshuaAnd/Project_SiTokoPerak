@extends('adminlte::page')

@section('title', 'Create Data Usaha - Produk')

@section('content_header')
    <h1>Create Data Usaha - Produk</h1>
@stop

@section('content')
<div class="container">
    <form action="{{ route('admin.usaha_produk-store') }}" method="POST" id="createUsahaProdukForm">
        @csrf
        <!-- Nama Usaha -->
        <div class="form-group">
            <label for="usaha_id">Nama Usaha</label>
            <select class="form-control" id="usaha_id" name="usaha_id" required>
                <option value="">Pilih Nama Usaha</option>
                @foreach ($usahas as $usaha)
                    <option value="{{ $usaha->id }}">{{ $usaha->nama_usaha }}</option>
                @endforeach
            </select>
        </div>

        <!-- Nama Produk -->
        <div class="form-group">
            <label for="produk_id">Nama Produk</label>
            <select class="form-control" id="produk_id" name="produk_id" required disabled>
                <option value="">Pilih Nama Usaha Terlebih Dahulu</option>
            </select>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="btn btn-primary">Simpan Data</button>
        <a href="{{ route('admin.usaha_produk-index') }}" class="btn btn-secondary">Batal</a>
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
        document.getElementById('createUsahaProdukForm').addEventListener('submit', function(e) {
            // Contoh validasi dasar: memastikan produk tidak kosong
            if (document.getElementById('produk_id').value.trim() === '') {
                alert('Kode Produk wajib dipilih!');
                e.preventDefault();
                return false;
            }
            // Contoh validasi dasar: memastikan usaha_id tidak kosong
            if (document.getElementById('usaha_id').value.trim() === '') {
                alert('Usaha wajib dipilih!');
                e.preventDefault();
                return false;
            }

            // usaha_id dan produk_id tidak boleh sama dengan data sebelumnya
            var usahaId = parseInt(document.getElementById('usaha_id').value);
            var jenisUsahaId = parseInt(document.getElementById('produk_id').value);
            var existingUsahaId = {{ $existingUsahaId ?? 'null' }};
            var existingProdukId = {{ $existingProdukId ?? 'null' }};

            if (usahaId === existingUsahaId && jenisUsahaId === existingProdukId) {
                alert('Data sudah ada sebelumnya!');
                e.preventDefault();
                return false;
            }


        });
        // JavaScript untuk memuat produk berdasarkan usaha yang dipilih
        document.getElementById('usaha_id').addEventListener('change', function() {
            var usahaId = this.value;
            var produkSelect = document.getElementById('produk_id');
            produkSelect.innerHTML = '<option value="">Memuat Produk...</option>'; // Reset dan tampilkan loading
            produkSelect.disabled = true;

            if (usahaId) {
                fetch('/admin/get-produk-by-usaha/' + usahaId)
                    .then(response => response.json())
                    .then(data => {
                        produkSelect.innerHTML = '<option value="">Pilih Nama Produk</option>';
                        data.forEach(function(produk) {
                            var option = document.createElement('option');
                            option.value = produk.id;
                            option.textContent = produk.nama_produk;
                            produkSelect.appendChild(option);
                        });
                        produkSelect.disabled = false;
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                produkSelect.innerHTML = '<option value="">Pilih Nama Usaha Terlebih Dahulu</option>';
            }
        });


        console.log("Form Create Pengerajin loaded");
    </script>
@stop
