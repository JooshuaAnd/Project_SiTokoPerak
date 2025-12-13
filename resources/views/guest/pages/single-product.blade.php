@extends('guest.layouts.main')

@section('title', $produk->nama_produk)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/detail-product.css') }}">
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

                        {{-- Thumbnail --}}
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

                        {{-- HEADER: judul + icon --}}
                        <div class="product-header d-flex justify-content-between align-items-start">
                            <h2 class="product-title">{{ $produk->nama_produk }}</h2>
                            <div class="action-icons d-flex gap-2">
                                {{-- LIKE utama --}}
                                <button type="button" class="btn btn-icon like-btn" data-id="{{ $produk->id }}">
                                    <i class="fa fa-star star-icon {{ $produk->is_liked ? 'active' : '' }}"></i>
                                </button>

                                {{-- Share --}}
                                <button class="btn btn-icon" id="copyLinkBtn" title="Bagikan Tautan">
                                    <i class="fa fa-share-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="rating-stock-wrapper">
                            <div class="rating-wrapper">
                                <div class="stars-custom">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                </div>
                            </div>
                            <span class="stock-status">IN STOCK</span>
                        </div>

                        <span class="price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>

                        {{-- Views & Likes --}}
                        <p class="product-meta text-muted mb-2">
                            {{ $produk->views_count ?? 0 }}x dilihat • {{ $produk->likes_count ?? 0 }} suka
                        </p>

                        {{-- Info usaha --}}
                        @php
                            $usaha = $produk->usaha->first();
                        @endphp
                        <div class="usaha-info">
                            @if ($usaha)
                                <a href="{{ route('guest-detail-usaha', ['usaha' => $usaha, 'from_product' => $produk->slug]) }}"
                                    class="usaha-link">
                                    <img src="{{ asset('assets/images/kategori-default.jpg') }}" alt="Logo Usaha"
                                        class="usaha-avatar">
                                    <div class="usaha-details">
                                        <span class="usaha-name">{{ $usaha->nama_usaha }}</span>
                                        <span class="usaha-spesialisasi">
                                            {{ $usaha->deskripsi_usaha ?? 'Kerajinan Perak Kotagede' }}
                                        </span>
                                    </div>
                                </a>
                                <div class="social-links">
                                    <a href="#" target="_blank" class="social-icon email" title="Email"><i
                                            class="fa fa-envelope"></i></a>
                                    <a href="https://wa.me/" target="_blank" class="social-icon whatsapp"
                                        title="WhatsApp"><i class="fa fa-phone"></i></a>
                                    <a href="#" target="_blank" class="social-icon instagram" title="Instagram"><i
                                            class="fa fa-instagram"></i></a>
                                    <a href="#" target="_blank" class="social-icon tiktok" title="TikTok"><i
                                            class="fa fa-tiktok"></i></a>
                                    <a href="#" target="_blank" class="social-icon shopee" title="Shopee">
                                        <img src="{{ asset('assets/images/shopee-icon.png') }}" alt="Shopee">
                                    </a>
                                    <a href="#" target="_blank" class="social-icon tokped" title="Tokopedia">
                                        <img src="{{ asset('assets/images/tokopedia-icon.png') }}" alt="Tokopedia">
                                    </a>
                                </div>
                            @else
                                <p class="text-muted">Informasi usaha tidak tersedia.</p>
                            @endif
                        </div>

                        {{-- DESKRIPSI --}}
                        <div class="product-details">
                            <h5>Detail</h5>
                            <p>{{ $produk->deskripsi }}</p>
                        </div>

                        {{-- ADD TO CART --}}
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

    {{-- ==================== PRODUK TERKAIT ==================== --}}
    <section class="products">
        <div class="container">
            <div class="section-heading">
                <h2>Produk Terkait</h2>
            </div>
        </div>

        <div class="container">
            <div class="row">
                @foreach ($randomProduks as $relatedProduk)
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-item">
                            <div class="thumb">
                                <a href="{{ route('guest-singleProduct', $relatedProduk->slug) }}"
                                    class="img-link btn-show" data-id="{{ $relatedProduk->id }}">
                                    <img src="{{ asset('storage/' . optional($relatedProduk->fotoProduk->first())->file_foto_produk) }}"
                                        alt="{{ $relatedProduk->nama_produk }}"
                                        onerror="this.onerror=null;this.src='{{ asset('images/produk-default.jpg') }}';">
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
                                            <button type="button" class="like-btn" data-id="{{ $relatedProduk->id }}">
                                                <i
                                                    class="fa fa-star star-icon {{ $relatedProduk->is_liked ? 'active' : '' }}"></i>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="add-cart-btn"
                                                data-id="{{ $relatedProduk->id }}">
                                                <i class="fa fa-shopping-cart"></i>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="down-content">
                                <h4>{{ $relatedProduk->nama_produk }}</h4>
                                <span class="product-price">Rp
                                    {{ number_format($relatedProduk->harga, 0, ',', '.') }}</span>
                                <ul class="stars">
                                    @for ($i = 0; $i < 5; $i++)
                                        <li><i class="fa fa-star"></i></li>
                                    @endfor
                                </ul>
                                <p class="product-reviews">
                                    {{ $relatedProduk->views_count ?? 0 }}x dilihat •
                                    {{ $relatedProduk->likes_count ?? 0 }} suka
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

    {{-- ==================== REVIEW SECTION (punyamu sendiri) ==================== --}}
    {{-- BAGIAN ULASAN TIDAK AKU UBAH – tetap seperti kode kamu sebelumnya --}}
    {{-- ... bagian reviews yang panjangmu tetap bisa ditempel di sini ... --}}

@endsection

@push('scripts')
    <script>
        // --- fungsi ganti gambar utama dari thumbnail (punyamu) ---
        function changeMainImage(thumbnailElement) {
            const index = Array.from(document.querySelectorAll('.thumbnail-item')).indexOf(thumbnailElement.parentElement);
            if (index > -1) {
                updateGallery(index);
            }
        }

        function updateGallery(index) {
            const thumbnails = document.querySelectorAll('.thumbnail-item');
            const mainImage = document.getElementById('mainProductImage');
            const mainImageLink = mainImage ? mainImage.parentElement : null;

            if (thumbnails.length === 0 || !mainImage) return;

            const newImageSrc = thumbnails[index].querySelector('img').src;

            mainImage.src = newImageSrc;
            if (mainImageLink) {
                mainImageLink.href = newImageSrc;
            }

            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            thumbnails[index].classList.add('active');

            window.currentImageIndex = index;
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.currentImageIndex = 0;

            const thumbContainer = document.getElementById('thumbnailContainer');
            const thumbPrevBtn = document.getElementById('thumbPrevBtn');
            const thumbNextBtn = document.getElementById('thumbNextBtn');

            if (thumbContainer && thumbPrevBtn && thumbNextBtn) {
                thumbNextBtn.addEventListener('click', () => {
                    let nextIndex = window.currentImageIndex + 1;
                    if (nextIndex >= thumbContainer.children.length) nextIndex = 0;
                    updateGallery(nextIndex);
                    thumbContainer.children[nextIndex].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                });

                thumbPrevBtn.addEventListener('click', () => {
                    let prevIndex = window.currentImageIndex - 1;
                    if (prevIndex < 0) prevIndex = thumbContainer.children.length - 1;
                    updateGallery(prevIndex);
                    thumbContainer.children[prevIndex].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                });
            }

            // Copy link
            const copyBtn = document.getElementById('copyLinkBtn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    const urlToCopy = window.location.href;
                    navigator.clipboard.writeText(urlToCopy).then(() => {
                        const icon = copyBtn.querySelector('i');
                        const originalClass = icon.className;
                        icon.className = 'fa fa-check';
                        copyBtn.disabled = true;
                        setTimeout(() => {
                            icon.className = originalClass;
                            copyBtn.disabled = false;
                        }, 2000);
                    }).catch(err => console.error('Gagal menyalin:', err));
                });
            }

            // === LIKE untuk produk utama & produk terkait ===
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const productId = this.dataset.id;
                    const icon = this.querySelector('.star-icon');
                    const card = this.closest('.product-item') || document.querySelector(
                        '.right-content');
                    const infoText = card?.querySelector('.product-reviews');

                    fetch(`/produk/${productId}/like`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({})
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.liked) {
                                icon.classList.add('active');
                            } else {
                                icon.classList.remove('active');
                            }

                            if (infoText && typeof data.totalLikes !== 'undefined') {
                                const parts = infoText.textContent.split('•');
                                const viewsPart = parts[0].trim();
                                infoText.textContent = `${viewsPart} • ${data.totalLikes} suka`;
                            }

                            // update text meta di header kalau ada
                            const metaHeader = document.querySelector('.product-meta');
                            if (metaHeader && typeof data.totalLikes !== 'undefined') {
                                const text = metaHeader.textContent;
                                const pieces = text.split('•');
                                const left = pieces[0].trim(); // "...x dilihat"
                                metaHeader.textContent = `${left} • ${data.totalLikes} suka`;
                            }

                        })
                        .catch(err => console.error(err));
                });
            });

            // === VIEW untuk link dengan class .btn-show (produk terkait) ===
            document.querySelectorAll('.btn-show').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const productId = this.dataset.id;
                    const url = this.getAttribute('href');

                    fetch(`/produk/${productId}/view`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({})
                    }).catch(err => console.error(err));

                    window.location.href = url;
                });
            });
        });
    </script>
@endpush
