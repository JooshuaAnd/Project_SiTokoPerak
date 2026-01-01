@extends('guest.layouts.main')
@section('title', 'Index')

@section('content')

    {{-- ==================== BANNER ==================== --}}
    <div class="main-banner" id="top">
        <div class="banner-background">
            <img src="{{ asset('assets/images/malioboro2.jpg') }}" alt="Keraton Yogyakarta"
                style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: -1;">
        </div>

        <div class="banner-content">
            <h1>Perak Asli Kotagede – Warisan Seni dari Jogja</h1>
            <p>
                Karya seni perak dari Kotagede, Yogyakarta yang menggabungkan tradisi, ketelitian, dan keanggunan.
                Setiap detail menyimpan cerita, setiap ukiran merekam sejarah.
            </p>
            <a href="javascript:void(0);" class="btn btn-danger btn-lg mt-3 scroll-to-produk">Beli Sekarang</a>
        </div>
    </div>

    {{-- ==================== KATEGORI ==================== --}}
    <section class="categories">
        <div class="container">
            <div class="section-heading">
                <h2>Kategori Produk</h2>
                <p class="text-muted">Temukan berbagai koleksi produk perak terbaik kami</p>
            </div>

            <div id="categoryCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach ($kategoris->chunk(3) as $key => $chunk)
                        <div class="carousel-item @if ($key == 0) active @endif">
                            <div class="row">
                                @foreach ($chunk as $kategori)
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <a href="{{ route('guest-katalog', ['kategori' => $kategori->slug]) }}">
                                            <div class="card category-card h-100">
                                                <img src="{{ asset('assets/images/' . $kategori->slug . '.jpg') }}"
                                                    alt="{{ $kategori->nama_kategori_produk }}"
                                                    class="card-img-top category-img"
                                                    onerror="this.onerror=null;this.src='{{ asset('assets/images/kategori-default.jpg') }}';">
                                                <div class="card-body">
                                                    <h5 class="card-title">{{ $kategori->nama_kategori_produk }}</h5>
                                                    <p class="text-muted subtitle">Pesona Perak</p>
                                                    <span class="btn btn-outline-dark btn-sm">Lihat Produk</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#categoryCarousel"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#categoryCarousel"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </section>

    {{-- ==================== PRODUK TERBARU ==================== --}}
    <section class="products">
        <div class="container">
            <div class="section-heading">
                <h2>Produk Terbaru Kami!</h2>
                <span>Temukan Produk Terfavoritmu!</span>
            </div>
        </div>

        <div class="container">
            <div class="row">
                @foreach ($randomProduks as $produk)
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-item">
                            <div class="thumb">
                                {{-- Gambar Utama --}}
                                <a href="{{ route('guest-singleProduct', $produk->slug) }}" class="img-link btn-show"
                                    data-id="{{ $produk->id }}">
                                    <img src="{{ asset('storage/' . (optional($produk->fotoProduk->first())->file_foto_produk ?? 'placeholder.jpg')) }}"
                                        alt="{{ $produk->nama_produk }}"
                                        onerror="this.onerror=null;this.src='{{ asset('assets/images/produk-default.jpg') }}';">
                                </a>

                                {{-- HOVER CONTENT (Overlay Menu Tengah) --}}
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
                                                <a href="{{ route('login') }}">
                                                    <i class="fa fa-star star-icon"></i>
                                                </a>
                                            @endif
                                        </li>
                                        <li>
                                            <form action="{{ route('cart.add', $produk->slug) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="add-to-cart-btn-hover">
                                                    <i class="fa fa-shopping-cart"></i>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="down-content">
                                <h4>{{ $produk->nama_produk }}</h4>
                                <span class="product-price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                <p class="product-meta-text text-muted small mt-2">
                                    {{ $produk->views_count ?? 0 }}x dilihat • {{ $produk->likes_count ?? 0 }} suka
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-12">
                <div class="text-center mt-5">
                    <a href="{{ route('guest-katalog') }}" class="see-all-button btn">Lihat Semua</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== ABOUT ==================== --}}
    <section class="about-us">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-12">
                    <div class="about-image">
                        <img src="{{ asset('assets/images/kerajinan-perak-kota-ged.png') }}"
                            alt="Sentra Kerajinan Perak Kotagede" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-5 col-md-12">
                    <div class="about-content">
                        <h3>TekoPerakku</h3>
                        <p>TekoPerakku menghadirkan kerajinan perak asli Kotagede dengan kualitas terbaik yang dikerjakan
                            oleh tangan-tangan ahli untuk memastikan keindahan dan ketahanan produk.</p>
                        <a href="{{ route('guest-about') }}" class="btn btn-primary about-btn">Pelajari Lebih Lanjut</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== STYLE ==================== --}}
    <style>
        .product-item .thumb {
            position: relative;
            overflow: hidden;
            border-radius: 6px;
        }

        /* Overlay Menu Hover Tengah */
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
            margin: 0;
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
            text-decoration: none;
        }

        .product-item .hover-content ul li a:hover,
        .product-item .hover-content ul li button:hover {
            background: #212529;
            color: #fff;
        }

        /* Warna Bintang */
        .star-icon {
            color: #ccc;
            transition: color 0.3s ease;
        }

        .star-icon.active {
            color: #ffc107 !important;
        }
    </style>

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // --- Smooth Scroll ke Section Produk ---
            const scrollBtn = document.querySelector('.scroll-to-produk');
            if (scrollBtn) {
                scrollBtn.addEventListener('click', function() {
                    document.querySelector('.products')?.scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            }

            // --- LIKE AJAX ---
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const productId = this.dataset.id;
                    const icon = this.querySelector('.star-icon');
                    const infoText = this.closest('.product-item').querySelector(
                        '.product-meta-text');

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
                        })
                        .catch(err => console.error(err));
                });
            });

            // --- VIEW COUNTER AJAX ---
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
