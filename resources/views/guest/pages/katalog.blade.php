@extends('guest.layouts.main')
@section('title', 'Katalog')
@section('content')

    <div class="page-heading" id="top">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-content">
                        {{-- Teks disesuaikan dengan desain Figma --}}
                        <h2>Katalog Produk</h2>
                        <span>Penjelajahan tanpa batas, temukan produk kerajinan terbaik yang dibuat dengan penuh dedikasi
                            oleh para pengerajin lokal.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="section" id="products">
        <div class="container">
            <div class="row mb-5">
                @if (request()->filled('search'))
                    <div class="col-lg-12 search-heading">
                        <h2 class="search-title">Hasil Pencarian</h2>
                        {{-- Menampilkan jumlah hasil dan kata kunci pencarian secara dinamis --}}
                        <p class="result-count">
                            Menampilkan {{ $produks->firstItem() }} - {{ $produks->lastItem() }} dari
                            {{ $produks->total() }}
                            hasil untuk "<span class="search-term">{{ request('search') }}</span>"
                        </p>
                    </div>
                @endif
                <form action="{{ route('guest-katalog') }}" method="GET" class="w-100">
                    <div class="filters-wrapper">
                        <div class="filter-row">
                            {{-- Filter Kategori --}}
                            <div class="filter-group-custom">
                                <label for="kategoriDropdown">Kategori:</label>
                                @php
                                    $namaKategoriAktif = 'Semua Produk'; // Default
                                    if (request('kategori')) {
                                        // Cari koleksi kategori berdasarkan slug yang ada di URL
                                        $kategoriAktif = $kategoris->firstWhere('slug', request('kategori'));
                                        if ($kategoriAktif) {
                                            $namaKategoriAktif = $kategoriAktif->nama_kategori_produk;
                                        }
                                    }
                                @endphp
                                <div class="dropdown">
                                    <button
                                        class="form-select-custom dropdown-toggle {{ request('kategori') ? 'filter-active' : '' }}"
                                        type="button" id="kategoriDropdown" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        {{ $namaKategoriAktif }}
                                    </button>
                                    <div>
                                        <ul class="dropdown-menu" aria-labelledby="kategoriDropdown">

                                            <li>
                                                <a class="dropdown-item {{ !request('kategori') ? 'active' : '' }}"
                                                    href="{{ route('guest-katalog', request()->except('kategori')) }}">
                                                    Semua Produk
                                                </a>
                                            </li>
                                            @foreach ($kategoris as $kategori)
                                                <li>
                                                    <a class="dropdown-item {{ request('kategori') == $kategori->slug ? 'active' : '' }}"
                                                        href="{{ route('guest-katalog', array_merge(request()->except('page'), ['kategori' => $kategori->slug])) }}">
                                                        {{ $kategori->nama_kategori_produk }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-group-custom filter-group-dropdown">
                                <label for="harga-dropdown">Harga:</label>
                                <div class="dropdown w-100">
                                    <button
                                        class="btn-dropdown-custom {{ request('min_harga') || request('max_harga') ? 'filter-active' : '' }}"
                                        type="button" id="harga-dropdown" data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside" aria-expanded="false">
                                        @if (request('min_harga') && request('max_harga'))
                                            Rp {{ number_format(request('min_harga'), 0, ',', '.') }} - Rp
                                            {{ number_format(request('max_harga'), 0, ',', '.') }}
                                        @elseif (request('min_harga'))
                                            Diatas Rp {{ number_format(request('min_harga'), 0, ',', '.') }}
                                        @elseif (request('max_harga'))
                                            Dibawah Rp {{ number_format(request('max_harga'), 0, ',', '.') }}
                                        @else
                                            Semua Harga
                                        @endif
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-custom" aria-labelledby="harga-dropdown">
                                        <div class="price-range-form-new">
                                            <div class="price-inputs-wrapper">
                                                <div class="price-input-group-new">
                                                    <label for="min_harga" class="price-label-new">Min</label>
                                                    <input type="number" class="price-input" name="min_harga"
                                                        id="min_harga" placeholder="100.000"
                                                        value="{{ request('min_harga') }}">
                                                </div>
                                                <span class="price-separator">-</span>
                                                <div class="price-input-group-new">
                                                    <label for="max_harga" class="price-label-new">Maks</label>
                                                    <input type="number" class="price-input" name="max_harga"
                                                        id="max_harga" placeholder="1.000.000"
                                                        value="{{ request('max_harga') }}">
                                                </div>
                                            </div>

                                            @if (request('kategori'))
                                                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                                            @endif

                                            {{-- Ini untuk membawa nilai filter Urutkan yang sedang aktif --}}
                                            @if (request('urutkan'))
                                                <input type="hidden" name="urutkan" value="{{ request('urutkan') }}">
                                            @endif

                                            <div class="price-buttons-wrapper">
                                                <button type="submit" class="btn-apply-new">Terapkan</button>

                                                {{-- Tombol Reset menggunakan link (<a>) untuk menghapus parameter URL --}}
                                                <a href="{{ url()->current() }}?{{ http_build_query(request()->except(['min_harga', 'max_harga'])) }}"
                                                    class="btn-reset-new">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-group-custom">
                            <label for="urutkanDropdown">Urut Berdasarkan:</label>
                            @php
                                $opsiUrutkan = [
                                    'terbaru' => 'Produk Terbaru',
                                    'populer' => 'Popularitas',
                                    'harga-rendah' => 'Harga Terendah',
                                    'harga-tinggi' => 'Harga Tertinggi',
                                ];
                                $urutkanAktif = request('urutkan', 'terbaru');
                                $namaUrutkanAktif = $opsiUrutkan[$urutkanAktif] ?? 'Produk Terbaru';
                            @endphp

                            <div class="dropdown">
                                <button
                                    class="form-select-custom dropdown-toggle {{ request()->input('urutkan', 'terbaru') != 'terbaru' ? 'filter-active' : '' }}"
                                    type="button" id="urutkanDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ $namaUrutkanAktif }}
                                </button>

                                <ul class="dropdown-menu" aria-labelledby="urutkanDropdown">

                                    @foreach ($opsiUrutkan as $value => $text)
                                        <li>
                                            <a class="dropdown-item {{ $urutkanAktif == $value ? 'active' : '' }}"
                                                href="{{ route('guest-katalog', array_merge(request()->except(['page', 'urutkan']), ['urutkan' => $value])) }}">
                                                {{ $text }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- -- Akhir Bagian Filter Pencarian -- -->

            <div class="row">
                @forelse ($produks as $produk)
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-item">
                            <div class="thumb">
                                <a href="{{ route('guest-singleProduct', $produk->slug) }}" class="img-link btn-show"
                                    data-id="{{ $produk->id }}">
                                    <img src="{{ asset('storage/' . (optional($produk->fotoProduk->first())->file_foto_produk ?? 'placeholder.jpg')) }}"
                                        alt="{{ $produk->nama_produk }}">
                                </a>

                                <div class="hover-content">
                                    <ul>
                                        <li>
                                            <a href="{{ route('guest-singleProduct', $produk->slug) }}" class="btn-show"
                                                data-id="{{ $produk->id }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="like-btn" data-id="{{ $produk->id }}">
                                                <i
                                                    class="fa fa-star star-icon {{ $produk->is_liked ? 'active' : '' }}"></i>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="add-cart-btn" data-id="{{ $produk->id }}">
                                                <i class="fa fa-shopping-cart"></i>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="down-content">
                                <h4>
                                    <a href="{{ route('guest-singleProduct', $produk->slug) }}">
                                        {{ $produk->nama_produk }}
                                    </a>
                                </h4>
                                <span class="product-price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                <ul class="stars">
                                    @for ($i = 0; $i < 5; $i++)
                                        <li><i class="fa fa-star"></i></li>
                                    @endfor
                                </ul>
                                <p class="product-reviews">
                                    {{ $produk->views_count ?? 0 }}x dilihat • {{ $produk->likes_count ?? 0 }} suka
                                </p>

                                {{-- Tombol add-to-cart lama --}}
                                <form action="{{ route('cart.add', $produk->slug) }}" method="POST"
                                    onsubmit="this.querySelector('button').disabled = true;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="fa fa-shopping-cart"></i> Tambah
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p>Produk tidak ditemukan.</p>
                    </div>
                @endforelse
            </div>


            {{-- Bagian Paginasi Baru --}}
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="pagination">
                        {{-- Ini akan otomatis membuat link paginasi dari Laravel --}}
                        {{ $produks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cari form spesifik yang berisi filter
            const filterForm = document.querySelector('form[action="{{ route('guest-katalog') }}"]');

            // PENTING: Lanjutkan hanya jika form-nya ditemukan
            if (filterForm) {
                const filters = filterForm.querySelectorAll('select');

                filters.forEach(function(select) {
                    select.addEventListener('change', function() {
                        filterForm.submit();
                    });
                });
            }
        });
    </script>
    <style>
        .product-item .thumb,
        .item .thumb {
            position: relative;
            overflow: hidden;
            border-radius: 6px;
        }

        .product-item .hover-content,
        .item .hover-content {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 5;
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s ease, visibility .2s ease;
            pointer-events: none;
        }

        .product-item:hover .hover-content,
        .item:hover .hover-content {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .product-item .hover-content ul,
        .item .hover-content ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .product-item .hover-content ul li,
        .item .hover-content ul li {
            display: inline-block;
            margin-left: 5px;
        }

        .product-item .hover-content a,
        .product-item .hover-content button,
        .item .hover-content a,
        .item .hover-content button {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            color: #fff;
        }

        .star-icon {
            transition: .2s ease;
        }

        .star-icon.active {
            color: #ffc107 !important;
        }
    </style>
@endpush
