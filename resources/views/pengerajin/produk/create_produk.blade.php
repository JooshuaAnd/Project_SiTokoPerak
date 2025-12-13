@extends('pengerajin.layouts.pengerajin')

@section('content')
<div class="content-wrapper">
    <h2>Tambah Produk</h2>

    <form action="{{ route('pengerajin.produk.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label>Nama Produk</label>
        <input type="text" name="nama_produk" class="form-control mb-2" required>

        <label>Kode Produk</label>
        <input type="text" name="kode_produk" class="form-control mb-2" required>

        <label>Kategori Produk</label>
        <select name="kategori_produk_id" class="form-control mb-2" required>
            <option value="">-- Pilih Kategori --</option>
            @foreach ($kategori as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kategori_produk }}</option>
            @endforeach
        </select>

        <label>Harga</label>
        <input type="number" name="harga" class="form-control mb-2" required>

        <label>Stok</label>
        <input type="number" name="stok" class="form-control mb-2" required>

        <label>Gambar Produk</label>
        <input type="file" name="gambar" class="form-control mb-2">

        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control mb-3" rows="4"></textarea>

        <label>Slug</label>
        <textarea name="slug" class="form-control mb-3" rows="4"></textarea>

        <button class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
