@extends('guest.layouts.main')
@section('title', 'Berbagai Macam Produk')
@section('content')

    <div class="page-heading" id="top">
        <div class="container">
            <div class="inner-content">
                <h2>Temukan Produk Favoritmu!</h2>
                <span>Pilihan lengkap & harga terbaik hanya di toko kami</span>
            </div>
        </div>
    </div>

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
                    <div class="col-lg-4 mb-4">
                        <div class="item">
                            <div class="thumb">
                                <a href="{{ route('guest-singleProduct', $produk->slug) }}" class="img-link">
                                    <img src="{{ asset('storage/' . (optional($produk->fotoProduk->first())->file_foto_produk ?? 'placeholder.jpg')) }}"
                                        alt="{{ $produk->nama_produk }}"
                                        onerror="this.onerror=null;this.src='{{ asset('images/produk-default.jpg') }}';">
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
                                                    class="fa fa-star star-icon {{ ($produk->likes_count ?? 0) > 0 ? 'active' : '' }}"></i>
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
                                <span>Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                <p>{{ $produk->deskripsi }}</p>
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

    {{-- CSS hover sama seperti index --}}
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
            pointer-events: auto;
        }

        .product-item:hover .hover-content,
        .item:hover .hover-content {
            opacity: 1;
            visibility: visible;
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

@endsection

@push('scripts')
    {{-- JS like & view sama seperti index, bisa dipindah ke partial kalau mau --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const productId = this.dataset.id;
                    const icon = this.querySelector('.star-icon');
                    const infoText = this.closest('.item')?.querySelector('.small.text-muted');

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
                                const parts = infoText.innerText.split('•');
                                const viewsPart = parts[0] ?? '0x dilihat ';
                                infoText.innerText = viewsPart.trim() + ' • ' + (data
                                    .totalLikes ?? 0) + ' suka';
                            }
                        })
                        .catch(err => console.error(err));
                });
            });

            document.querySelectorAll('.btn-show').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const productId = this.dataset.id;
                    const url = this.getAttribute('href');
                    const infoText = this.closest('.item')?.querySelector('.small.text-muted');

                    fetch(`/produk/${productId}/view`, {
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
                            if (infoText && typeof data.totalViews !== 'undefined') {
                                const parts = infoText.innerText.split('•');
                                const likesPart = parts[1] ?? '0 suka';
                                infoText.innerText = (data.totalViews ?? 0) + 'x dilihat • ' +
                                    likesPart.trim();
                            }
                        })
                        .catch(err => console.error(err))
                        .finally(() => {
                            window.location.href = url;
                        });
                });
            });
        });
    </script>
@endpush
