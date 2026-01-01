@extends('guest.layouts.main')

@section('title', 'Berbagai Macam Produk')

@section('content')

    {{-- Header Halaman --}}
    <div class="page-heading" id="top">
        <div class="container">
            <div class="inner-content">
                <h2>Temukan Produk Favoritmu!</h2>
                <span>Pilihan lengkap & harga terbaik hanya di toko kami</span>
            </div>
        </div>
    </div>

    {{-- Daftar Produk --}}
    <section class="section" id="products">
        <div class="container">
            <div class="section-heading">
                <h2>Produk Terbaru Kami!</h2>
                <span>Temukan produk yang kamu suka!</span>
            </div>
        </div>

        <div class="container">
            <div class="row">
                @forelse ($produks as $produk)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="item">
                            <div class="thumb">
                                {{-- Link Gambar Utama --}}
                                <a href="{{ route('guest-singleProduct', $produk->slug) }}" class="img-link">
                                    <img src="{{ asset('storage/' . (optional($produk->fotoProduk->first())->file_foto_produk ?? 'placeholder.jpg')) }}"
                                        alt="{{ $produk->nama_produk }}"
                                        onerror="this.onerror=null;this.src='{{ asset('assets/images/produk-default.jpg') }}';">
                                </a>

                                {{-- HOVER CONTENT: Muncul saat mouse di atas gambar --}}
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
                                                        class="fa fa-star star-icon {{ ($produk->likes_count ?? 0) > 0 ? 'active' : '' }}"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}">
                                                    <i class="fa fa-star star-icon"></i>
                                                </a>
                                            @endif
                                        </li>
                                        <li>
                                            <form action="{{ route('cart.add', $produk->slug) }}" method="POST">
                                                @csrf
                                                <button type="submit">
                                                    <i class="fa fa-shopping-cart"></i>
                                                </button>
                                            </form>
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
                                <span>Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                <p>{{ Str::limit($produk->deskripsi, 100) }}</p>
                                <p class="small text-muted mb-0">
                                    {{ $produk->views_count ?? 0 }}x dilihat • {{ $produk->likes_count ?? 0 }} suka
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <h5>Produk belum tersedia saat ini.</h5>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Styling untuk Hover & Warna Bintang --}}
    <style>
        /* Container Gambar */
        .item .thumb {
            position: relative;
            overflow: hidden;
            border-radius: 6px;
        }

        /* Overlay Hitam Transparan saat Hover */
        .item .hover-content {
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

        /* Tampilkan saat di-hover */
        .item:hover .hover-content {
            bottom: 0;
            opacity: 1;
            visibility: visible;
        }

        .item .hover-content ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 10px;
        }

        /* Desain Bulatan Icon */
        .item .hover-content ul li a,
        .item .hover-content ul li button {
            width: 40px;
            height: 40px;
            background-color: #fff;
            color: #2a2a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .item .hover-content ul li a:hover,
        .item .hover-content ul li button:hover {
            background-color: #212529;
            color: #fff;
        }

        /* Warna Bintang (Like/Favorite) */
        .star-icon {
            color: #ccc;
            transition: color 0.3s ease;
        }

        .star-icon.active {
            color: #ffc107 !important;
            /* Kuning Emas */
        }
    </style>

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            // --- Fungsi Like (AJAX) ---
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const productId = this.dataset.id;
                    const icon = this.querySelector('.star-icon');
                    const infoText = this.closest('.item')?.querySelector('.small.text-muted');

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
                            // Toggle Class Active
                            if (data.liked) {
                                icon.classList.add('active');
                            } else {
                                icon.classList.remove('active');
                            }

                            // Update Text Jumlah Suka di Bawah Gambar
                            if (infoText && typeof data.totalLikes !== 'undefined') {
                                const parts = infoText.innerText.split('•');
                                const viewsPart = parts[0] ?? '0x dilihat ';
                                infoText.innerText =
                                    `${viewsPart.trim()} • ${data.totalLikes} suka`;
                            }
                        })
                        .catch(err => console.error('Gagal memproses like:', err));
                });
            });

            // --- Fungsi View Counter saat Klik Mata/Gambar ---
            document.querySelectorAll('.btn-show, .img-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const productId = this.dataset.id || this.closest('.item').querySelector(
                        '.btn-show').dataset.id;
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
