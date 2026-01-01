@extends('guest.layouts.main')

@section('title', 'Katalog Produk')

@section('content')
    {{-- Page Heading --}}
    <div class="page-heading" id="top">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-content">
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
            {{-- Bagian Judul Pencarian --}}
            <div class="row mb-5">
                @if (request()->filled('search'))
                    <div class="col-lg-12 search-heading">
                        <h2 class="search-title">Hasil Pencarian</h2>
                        <p class="result-count">
                            Menampilkan {{ $produks->firstItem() }} - {{ $produks->lastItem() }} dari
                            {{ $produks->total() }}
                            hasil untuk "<span class="search-term">{{ request('search') }}</span>"
                        </p>
                    </div>
                @endif

                {{-- Form Filter --}}
                <form action="{{ route('guest-katalog') }}" method="GET" class="w-100">
                    <div class="filters-wrapper">
                        <div class="filter-row">
                            {{-- Filter Kategori --}}
                            <div class="filter-group-custom">
                                <label>Kategori:</label>
                                @php
                                    $namaKategoriAktif = 'Semua Produk';
                                    if (request('kategori')) {
                                        $kategoriAktif = $kategoris->firstWhere('slug', request('kategori'));
                                        if ($kategoriAktif) {
                                            $namaKategoriAktif = $kategoriAktif->nama_kategori_produk;
                                        }
                                    }
                                @endphp
                                <div class="dropdown">
                                    <button
                                        class="form-select-custom dropdown-toggle {{ request('kategori') ? 'filter-active' : '' }}"
                                        type="button" id="kategoriDropdown" data-bs-toggle="dropdown">
                                        {{ $namaKategoriAktif }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item {{ !request('kategori') ? 'active' : '' }}"
                                                href="{{ route('guest-katalog', request()->except('kategori')) }}">Semua
                                                Produk</a></li>
                                        @foreach ($kategoris as $kategori)
                                            <li><a class="dropdown-item {{ request('kategori') == $kategori->slug ? 'active' : '' }}"
                                                    href="{{ route('guest-katalog', array_merge(request()->except('page'), ['kategori' => $kategori->slug])) }}">{{ $kategori->nama_kategori_produk }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            {{-- Filter Harga --}}
                            <div class="filter-group-custom filter-group-dropdown">
                                <label>Harga:</label>
                                <div class="dropdown w-100">
                                    <button
                                        class="btn-dropdown-custom {{ request('min_harga') || request('max_harga') ? 'filter-active' : '' }}"
                                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                        @if (request('min_harga') && request('max_harga'))
                                            Rp {{ number_format(request('min_harga'), 0, ',', '.') }} - Rp
                                            {{ number_format(request('max_harga'), 0, ',', '.') }}
                                        @else
                                            Semua Harga
                                        @endif
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-custom">
                                        <div class="price-range-form-new">
                                            <div class="price-inputs-wrapper">
                                                <input type="number" name="min_harga" class="price-input" placeholder="Min"
                                                    value="{{ request('min_harga') }}">
                                                <span class="price-separator">-</span>
                                                <input type="number" name="max_harga" class="price-input"
                                                    placeholder="Maks" value="{{ request('max_harga') }}">
                                            </div>
                                            <div class="price-buttons-wrapper">
                                                <button type="submit" class="btn-apply-new">Terapkan</button>
                                                <a href="{{ url()->current() }}?{{ http_build_query(request()->except(['min_harga', 'max_harga'])) }}"
                                                    class="btn-reset-new">Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Filter Urutkan --}}
                        <div class="filter-group-custom">
                            <label>Urut Berdasarkan:</label>
                            @php
                                $opsiUrutkan = [
                                    'terbaru' => 'Produk Terbaru',
                                    'populer' => 'Popularitas',
                                    'harga-rendah' => 'Harga Terendah',
                                    'harga-tinggi' => 'Harga Tertinggi',
                                ];
                                $urutkanAktif = request('urutkan', 'terbaru');
                            @endphp
                            <div class="dropdown">
                                <button class="form-select-custom dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    {{ $opsiUrutkan[$urutkanAktif] ?? 'Produk Terbaru' }}
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach ($opsiUrutkan as $val => $txt)
                                        <li><a class="dropdown-item {{ $urutkanAktif == $val ? 'active' : '' }}"
                                                href="{{ route('guest-katalog', array_merge(request()->except('urutkan'), ['urutkan' => $val])) }}">{{ $txt }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Grid Produk --}}
            <div class="row">
                @forelse ($produks as $produk)
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-item">
                            <div class="thumb">
                                {{-- Link Utama --}}
                                <a href="{{ route('guest-singleProduct', $produk->slug) }}" class="img-link btn-show"
                                    data-id="{{ $produk->id }}">
                                    <img src="{{ asset('storage/' . (optional($produk->fotoProduk->first())->file_foto_produk ?? 'placeholder.jpg')) }}"
                                        alt="{{ $produk->nama_produk }}">
                                </a>

                                {{-- Hover Content (Menu Tengah) --}}
                                <div class="hover-content">
                                    <ul>
                                        <li>
                                            <a href="{{ route('guest-singleProduct', $produk->slug) }}" class="btn-show"
                                                data-id="{{ $produk->id }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </li>
                                        <li>
                                            @if (auth()->check())
                                                <button type="button" class="like-btn" data-id="{{ $produk->id }}">
                                                    <i
                                                        class="fa fa-star star-icon {{ $produk->is_liked ? 'active' : '' }}"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}"><i class="fa fa-star star-icon"></i></a>
                                            @endif
                                        </li>
                                        <li>
                                            <form action="{{ route('cart.add', $produk->slug) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"><i class="fa fa-shopping-cart"></i></button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="down-content">
                                <h4>
                                    <a
                                        href="{{ route('guest-singleProduct', $produk->slug) }}">{{ $produk->nama_produk }}</a>
                                </h4>
                                <span class="product-price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                <p class="product-meta text-muted small">
                                    {{ $produk->views_count ?? 0 }}x dilihat • {{ $produk->likes_count ?? 0 }} suka
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p>Produk tidak ditemukan.</p>
                    </div>
                @endforelse
            </div>

            {{-- Paginasi --}}
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="pagination">{{ $produks->links() }}</div>
                </div>
            </div>
        </div>
    </section>

    <style>
        /* Gaya Hover Content Center (Sama dengan Index) */
        .product-item .thumb {
            position: relative;
            overflow: hidden;
            border-radius: 6px;
        }

        .product-item .hover-content {
            position: absolute;
            bottom: -100px;
            /* Sembunyi di bawah */
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(42, 42, 42, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s ease;
            z-index: 5;
        }

        .product-item:hover .hover-content {
            bottom: 0;
            opacity: 1;
            visibility: visible;
        }

        .product-item .hover-content ul {
            list-style: none;
            display: flex;
            gap: 10px;
            padding: 0;
        }

        .product-item .hover-content ul li a,
        .product-item .hover-content ul li button {
            width: 40px;
            height: 40px;
            background: #fff;
            color: #2a2a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: none;
            transition: 0.3s;
        }

        .product-item .hover-content ul li a:hover,
        .product-item .hover-content ul li button:hover {
            background: #212529;
            color: #fff;
        }

        /* Warna Bintang Like */
        .star-icon {
            color: #ccc;
            transition: 0.3s;
        }

        .star-icon.active {
            color: #ffc107 !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Fitur Like AJAX ---
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const productId = this.dataset.id;
                    const icon = this.querySelector('.star-icon');
                    const infoText = this.closest('.product-item').querySelector('.product-meta');

                    fetch(`/produk/${productId}/like`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({})
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.liked) icon.classList.add('active');
                            else icon.classList.remove('active');

                            if (infoText && typeof data.totalLikes !== 'undefined') {
                                const viewsPart = infoText.innerText.split('•')[0].trim();
                                infoText.innerText = `${viewsPart} • ${data.totalLikes} suka`;
                            }
                        });
                });
            });

            // --- Fitur View Counter AJAX ---
            document.querySelectorAll('.btn-show, .img-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.dataset.id;
                    const url = this.getAttribute('href');

                    fetch(`/produk/${productId}/view`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({})
                    }).finally(() => {
                        window.location.href = url;
                    });
                });
            });
        });
    </script>
@endpush
