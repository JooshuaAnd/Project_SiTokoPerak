@extends('guest.layouts.main')

@section('title', $produk->nama_produk)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/detail-product.css') }}">
    <style>
        /* Perbaikan Warna Star Icon */
        .star-icon {
            color: #ccc;
            /* Abu-abu saat unlike */
            transition: all 0.3s ease;
            cursor: pointer;
        }

        /* Dipaksa menggunakan !important agar tidak tertimpa class bawaan library */
        .star-icon.active {
            color: #ffc107 !important;
            /* Kuning emas saat like */
        }

        /* Efek Hover untuk Produk Terkait */
        .product-item {
            position: relative;
            overflow: hidden;
            border: 1px solid #eee;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .product-item .thumb {
            position: relative;
            overflow: hidden;
        }

        .product-item .hover-content {
            position: absolute;
            bottom: -100px;
            /* Sembunyi di bawah */
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px 0;
            text-align: center;
            transition: all 0.4s ease-in-out;
            opacity: 0;
            visibility: hidden;
            z-index: 10;
        }

        .product-item:hover .hover-content {
            bottom: 0;
            /* Muncul ke atas */
            opacity: 1;
            visibility: visible;
        }

        .product-item .hover-content ul {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .product-item .hover-content ul li {
            display: inline-block;
            margin: 0 8px;
        }

        .product-item .hover-content ul li a,
        .product-item .hover-content ul li button {
            width: 40px;
            height: 40px;
            line-height: 40px;
            background: #fff;
            color: #333;
            display: block;
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border: none;
        }

        .product-item .hover-content ul li a:hover,
        .product-item .hover-content ul li button:hover {
            background: #212529;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    {{-- Breadcrumb --}}
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <nav aria-label="breadcrumb" class="product-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('guest-katalog') }}">Katalog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $produk->nama_produk }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- DETAIL PRODUK --}}
    <section class="section" id="product">
        <div class="container">
            <div class="row">
                {{-- Kiri: Galeri --}}
                <div class="col-lg-6">
                    <div class="gallery-wrapper">
                        <div class="main-image-container mb-3">
                            @if ($produk->fotoProduk->isNotEmpty())
                                <a href="{{ asset('storage/' . $produk->fotoProduk->first()->file_foto_produk) }}"
                                    data-lightbox="product-gallery">
                                    <img src="{{ asset('storage/' . $produk->fotoProduk->first()->file_foto_produk) }}"
                                        alt="{{ $produk->nama_produk }}" id="mainProductImage" class="img-fluid">
                                </a>
                            @else
                                <a href="{{ asset('assets/images/produk-default.jpg') }}" data-lightbox="product-gallery">
                                    <img src="{{ asset('assets/images/produk-default.jpg') }}" alt="Produk Default"
                                        id="mainProductImage" class="img-fluid">
                                </a>
                            @endif
                        </div>

                        <div class="thumbnail-scroller-wrapper">
                            <button class="thumb-nav-btn prev" id="thumbPrevBtn"><i class="fa fa-chevron-left"></i></button>
                            <div class="thumbnail-container" id="thumbnailContainer">
                                @foreach ($produk->fotoProduk as $index => $foto)
                                    <div class="thumbnail-item {{ $index == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $foto->file_foto_produk) }}" alt="Thumbnail"
                                            class="img-fluid" onclick="changeMainImage(this)">
                                    </div>
                                @endforeach
                            </div>
                            <button class="thumb-nav-btn next" id="thumbNextBtn"><i
                                    class="fa fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>

                {{-- Kanan: Info Produk --}}
                <div class="col-lg-6">
                    <div class="right-content">
                        <div class="product-header d-flex justify-content-between align-items-start">
                            <h2 class="product-title">{{ $produk->nama_produk }}</h2>
                            <div class="action-icons d-flex gap-2">
                                <button type="button" class="btn btn-icon like-btn" data-id="{{ $produk->id }}">
                                    <i class="fa fa-star star-icon {{ $produk->is_liked ? 'active' : '' }}"></i>
                                </button>
                                <button class="btn btn-icon" id="copyLinkBtn" title="Bagikan Tautan">
                                    <i class="fa fa-share-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="rating-stock-wrapper">
                            <div class="rating-wrapper">
                                <div class="stars-custom">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa fa-star"></i>
                                    @endfor
                                </div>
                            </div>
                            <span class="stock-status">IN STOCK</span>
                        </div>

                        <span class="price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>

                        <p class="product-meta text-muted mb-2">
                            {{ $produk->views_count ?? 0 }}x dilihat • {{ $produk->likes_count ?? 0 }} suka
                        </p>

                        @php $usaha = $produk->usaha->first(); @endphp
                        <div class="usaha-info">
                            @if ($usaha)
                                <a href="{{ route('guest-detail-usaha', ['usaha' => $usaha, 'from_product' => $produk->slug]) }}"
                                    class="usaha-link">
                                    <img src="{{ asset('assets/images/kategori-default.jpg') }}" alt="Logo"
                                        class="usaha-avatar">
                                    <div class="usaha-details">
                                        <span class="usaha-name">{{ $usaha->nama_usaha }}</span>
                                        <span
                                            class="usaha-spesialisasi">{{ $usaha->deskripsi_usaha ?? 'Kerajinan Perak Kotagede' }}</span>
                                    </div>
                                </a>
                            @endif
                        </div>

                        <div class="product-details">
                            <h5>Detail</h5>
                            <p>{{ $produk->deskripsi }}</p>
                        </div>

                        <form action="{{ route('cart.add', $produk->slug) }}" method="POST"
                            onsubmit="this.querySelector('button').disabled = true;">
                            @csrf
                            <button type="submit" class="see-all-button btn">
                                <i class="fa fa-shopping-cart me-2"></i> Tambah ke Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PRODUK TERKAIT --}}
    <section class="products">
        <div class="container">
            <div class="section-heading">
                <h2>Produk Terkait</h2>
            </div>
            <div class="row">
                @foreach ($randomProduks as $relatedProduk)
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-item">
                            <div class="thumb">
                                <a href="{{ route('guest-singleProduct', $relatedProduk->slug) }}"
                                    class="img-link btn-show" data-id="{{ $relatedProduk->id }}">
                                    <img src="{{ asset('storage/' . optional($relatedProduk->fotoProduk->first())->file_foto_produk) }}"
                                        alt="{{ $relatedProduk->nama_produk }}"
                                        onerror="this.src='{{ asset('assets/images/produk-default.jpg') }}';">
                                </a>

                                <div class="hover-content">
                                    <ul>
                                        <li>
                                            <a href="{{ route('guest-singleProduct', $relatedProduk->slug) }}"
                                                class="btn-show" data-id="{{ $relatedProduk->id }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </li>
                                        <li>
                                            @if (auth()->check())
                                                <button type="button" class="like-btn"
                                                    data-id="{{ $relatedProduk->id }}">
                                                    <i
                                                        class="fa fa-star star-icon {{ $relatedProduk->is_liked ? 'active' : '' }}"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}"><i class="fa fa-star star-icon"></i></a>
                                            @endif
                                        </li>
                                        <li>
                                            <form action="{{ route('cart.add', $relatedProduk->slug) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"><i class="fa fa-shopping-cart"></i></button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="down-content">
                                <h4>{{ $relatedProduk->nama_produk }}</h4>
                                <span class="product-price">Rp
                                    {{ number_format($relatedProduk->harga, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- REVIEWS SECTION --}}
    <section class="section" id="reviews">
        <div class="container">
            <div class="section-heading">
                <h2>Ulasan dan Rating</h2>
            </div>
            <div class="review-summary-container">
                <div class="rating-summary">
                    <span class="rating-value">{{ $produk->average_rating }}</span> dari 5
                </div>
                {{-- ... bagian filter ulasan ... --}}
            </div>
            {{-- Loop Reviews --}}
            <div class="reviews-wrapper">
                @forelse ($reviews as $review)
                    <div class="review-item">
                        <h6>{{ $review->user->name }} <small>({{ $review->created_at->format('d M Y') }})</small></h6>
                        <p>{{ $review->komentar }}</p>
                    </div>
                @empty
                    <p>Belum ada ulasan.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function changeMainImage(thumbnailElement) {
            const index = Array.from(document.querySelectorAll('.thumbnail-item')).indexOf(thumbnailElement.parentElement);
            if (index > -1) updateGallery(index);
        }

        function updateGallery(index) {
            const thumbnails = document.querySelectorAll('.thumbnail-item');
            const mainImage = document.getElementById('mainProductImage');
            if (thumbnails.length === 0 || !mainImage) return;

            const newImageSrc = thumbnails[index].querySelector('img').src;
            mainImage.src = newImageSrc;
            if (mainImage.parentElement.tagName === 'A') mainImage.parentElement.href = newImageSrc;

            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            thumbnails[index].classList.add('active');
            window.currentImageIndex = index;
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.currentImageIndex = 0;

            // Like Button Handler (Main & Related)
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const productId = this.dataset.id;
                    const icon = this.querySelector('.star-icon');

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
                            // Logic Toggle Warna
                            if (data.liked) {
                                icon.classList.add('active');
                            } else {
                                icon.classList.remove('active');
                            }

                            // Update Text Meta di Header (Jika ada)
                            const metaHeader = document.querySelector('.product-meta');
                            if (metaHeader && typeof data.totalLikes !== 'undefined') {
                                const currentText = metaHeader.textContent.split('•');
                                metaHeader.textContent =
                                    `${currentText[0].trim()} • ${data.totalLikes} suka`;
                            }
                        })
                        .catch(err => console.error(err));
                });
            });

            // View Counter Handler
            document.querySelectorAll('.btn-show').forEach(link => {
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
