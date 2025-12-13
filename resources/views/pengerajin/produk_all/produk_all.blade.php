@extends('pengerajin.layouts.pengerajin')

@section('content')

<div class="content-wrapper">
    <h2 class="mb-4">Produk All</h2>



    <div class="row g-4">

        @forelse ($produk as $item)
            <div class="col-md-4">
                <div class="product-card">

                    <img src="{{ $item->gambar ? asset('storage/' . $item->gambar) : 'https://via.placeholder.com/300x200?text=No+Image' }}"
                         class="product-img">

                    <div class="p-3">
                        <h5 class="fw-bold">{{ $item->nama_produk }}</h5>
                        <p class="text-muted">{{ $item->kategoriProduk->nama_kategori_produk ?? '-' }}</p>

                        <strong>Rp {{ number_format($item->harga,0,',','.') }}</strong>

                        
                    </div>

                </div>
            </div>

        @empty
            <div class="col-12 text-center py-4">
                <h5>Belum ada produk</h5>
            </div>
        @endforelse

    </div>
</div>

@endsection
