@extends('guest.layouts.main')

@section('title', 'Profil Usaha - ' . $usaha->nama_usaha)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/detail-usaha.css') }}">
@endpush

@section('content')

{{-- Breadcrumb Navigation --}}
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <nav aria-label="breadcrumb" class="product-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('guest-katalog') }}">Katalog</a></li>

                    {{-- Cek apakah ada data produk sebelumnya --}}
                    @if (isset($previousProduct))
                        <li class="breadcrumb-item">
                            <a href="{{ route('guest-singleProduct', $previousProduct->slug) }}">{{ $previousProduct->nama_produk }}</a>
                        </li>
                    @endif

                    <li class="breadcrumb-item active" aria-current="page">{{ $usaha->nama_usaha }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<section class="detail-pengrajin-section" style="padding-top: 20px;">
    <div class="container">
        <div class="row">

            {{-- Kolom Kiri: Sidebar Profil Usaha & Daftar Pengrajin --}}
            <div class="col-lg-5 col-md-5">
                <aside class="pengrajin-sidebar">
                    <div class="profil-foto-wrapper">
                        <img src="{{ asset('storage/' . $usaha->foto_usaha) }}"
                             alt="Logo {{ $usaha->nama_usaha }}"
                             class="profil-foto"
                             onerror="this.onerror=null;this.src='{{ asset('assets/images/kategori-default.jpg') }}';">
                    </div>
                    <div class="profil-info">
                        <h3 class="nama-pengrajin">{{ $usaha->nama_usaha }}</h3>
                        <p class="spesialisasi">{{ $usaha->deskripsi_usaha ?? 'Kerajinan Perak Kotagede' }}</p>
                    </div>
                    <ul class="kontak-list">
                        <li><i class="fa fa-envelope"></i>{{ $usaha->email_usaha ?? '' }}</li>
                        <li><i class="fa fa-phone"></i>{{ $usaha->telp_usaha ?? '' }}</li>
                    </ul>
                    <div class="alamat-info">
                        <i class="fa fa-map-marker"></i>
                        <a href="{{ $usaha->link_gmap_usaha }}">{{ $usaha->link_gmap_usaha ?? 'Alamat tidak tersedia.' }}</a>
                    </div>

                    <div class="pengrajin-list-wrapper">
                        <h5 class="list-title">Pengrajin:</h5>
                        @forelse ($usaha->pengerajins as $pengrajin)
                            <div class="pengrajin-list-item">
                                <img src="{{ asset('storage/' . $pengrajin->foto_pengrajin) }}"
                                     alt="{{ $pengrajin->nama_pengrajin }}"
                                     class="pengrajin-avatar"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/images/instagram-05.jpg') }}';">
                                <div class="pengrajin-contact">
                                    <span class="nama">{{ $pengrajin->nama_pengerajin }}</span>
                                    <span class="email">{{ $pengrajin->email_pengerajin }}</span>
                                </div>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $pengrajin->telp_pengerajin) }}" target="_blank" class="wa-button">
                                    <i class="fa fa-whatsapp"></i>
                                </a>
                            </div>
                        @empty
                            <p class="text-muted">Belum ada data pengrajin untuk usaha ini.</p>
                        @endforelse
                    </div>
                </aside>
            </div>

            {{-- Kolom Kanan: Galeri Produk dari Usaha Ini --}}
            <div class="col-lg-7 col-md-7">
                <div class="produk-grid">
                    <div class="row">
                        @forelse ($produks as $produk)
                            <div class="col-lg-6 col-md-12 col-sm-6 mb-4">
                                <div class="product-item">
                                    <a href="{{ route('guest-singleProduct', $produk->slug) }}">
                                        <div class="thumb">
                                            <img src="{{ asset('storage/' . optional($produk->fotoProduk->first())->file_foto_produk) }}"
                                                 alt="{{ $produk->nama_produk }}"
                                                 onerror="this.onerror=null;this.src='{{ asset('assets/images/kategori-default.jpg') }}';">
                                        </div>
                                        <div class="down-content">
                                            <h4>{{ $produk->nama_produk }}</h4>
                                            <span class="product-price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                            <ul class="stars">
                                                @for ($i = 0; $i < 5; $i++)
                                                    <li><i class="fa fa-star"></i></li>
                                                @endfor
                                            </ul>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-center">Belum ada produk dari usaha ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
