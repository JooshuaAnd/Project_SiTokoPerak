@extends('pengerajin.layouts.pengerajin')

@section('content')
<div class="content-wrapper">
    <h2>Edit Produk</h2>

    <form action="{{ route('pengerajin.produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT') <!-- penting untuk method spoofing -->

        <label>Nama Produk</label>
        <input type="text" name="nama_produk" class="form-control mb-2" value="{{ $produk->nama_produk }}" required>

        <label>Kode Produk</label>
        <input type="text" name="kode_produk" class="form-control mb-2" value="{{ $produk->kode_produk }}" required>

        <label>Kategori Produk</label>
        <select name="kategori_produk_id" class="form-control mb-2" required>
            @foreach ($kategori as $k)
                <option value="{{ $k->id }}" {{ $produk->kategori_produk_id == $k->id ? 'selected' : '' }}>
                    {{ $k->nama_kategori_produk }}
                </option>
            @endforeach
        </select>

        <label>Harga</label>
        <input type="number" name="harga" class="form-control mb-2" value="{{ $produk->harga }}" required>

        <label>Stok</label>
        <input type="number" name="stok" class="form-control mb-2" value="{{ $produk->stok }}" required>

        <label>Gambar Produk</label>
        <input type="file" name="gambar" class="form-control mb-2">

        <img src="{{ asset('storage/' . $produk->gambar) }}" class="mt-2" width="150">

        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control mb-3" rows="4">{{ $produk->deskripsi }}</textarea>

        <label>Slug</label>
        <textarea name="slug" class="form-control mb-3" rows="2">{{ $produk->slug }}</textarea>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
