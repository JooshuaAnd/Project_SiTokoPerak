@extends('pengerajin.layouts.pengerajin')

@section('content')

<div class="content-wrapper">

    <h2 class="mb-4">Produk Anda</h2>

    <a href="{{ route('pengerajin.produk.create') }}" class="btn btn-primary mb-3">+ Tambah Produk</a>

    <div class="row g-4">

        @forelse ($produk as $item)
            <div class="col-md-4">
                <div class="product-card">
                    <img src="{{ $item->gambar ? asset('storage/' . $item->gambar) : 'https://via.placeholder.com/400x300?text=No+Image' }}"
                        class="product-img">

                    <div class="p-3">
                        <h5 class="fw-bold">{{ $item->nama_produk }}</h5>
                        <p class="text-muted mb-1">{{ $item->kategoriProduk->nama_kategori_produk ?? 'Tidak ada kategori' }}</p>
                        <p class="small">{{ $item->deskripsi }}</p>

                        <div class="d-flex justify-content-between mt-2">
                            <strong>Rp {{ number_format($item->harga, 0, ',', '.') }}</strong>
                            <span class="badge bg-primary">Stok: {{ $item->stok }}</span>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('pengerajin.produk.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('pengerajin.produk.delete', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk?')">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>


                </div>
            </div>

        @empty
            <div class="col-12 text-center py-5">
                <h5>Belum ada produk.</h5>
            </div>
        @endforelse

    </div>

</div>

@endsection
