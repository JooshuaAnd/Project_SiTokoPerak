@extends('guest.layouts.main')
@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
    <div class="container py-5 mt-5">

        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Pesanan #{{ $order->order_number }}</h4>
                    </div>

                    <div class="card-body">

                        <!-- INFO PESANAN -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Tanggal:</strong> {{ $order->created_at->format('d F Y H:i') }}</p>
                                <p>
                                    <strong>Status:</strong>
                                    <span class="badge {{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </p>
                            </div>

                            <div class="col-md-6 text-md-end">
                                <p><strong>Total Pembayaran:</strong></p>
                                <h3 class="text-danger">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </h3>
                            </div>
                        </div>

                        <hr>

                        <!-- PRODUK -->
                        <h5>Produk yang Dibeli</h5>

                        <div class="table-responsive mb-4">
                            <table class="table">
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td width="60">
                                                @if($item->produk->fotoProduk->first())
                                                    <img src="{{ asset('storage/' . $item->produk->fotoProduk->first()->file_foto_produk) }}"
                                                        width="50" height="50" class="rounded" style="object-fit:cover;">
                                                @else
                                                    <img src="{{ asset('assets/images/produk-default.jpg') }}" width="50"
                                                        height="50" class="rounded">
                                                @endif
                                            </td>

                                            <td>
                                                {{ $item->produk->nama_produk }}
                                                <div class="small text-muted">
                                                    Harga: Rp {{ number_format($item->price_at_purchase, 0, ',', '.') }}
                                                    × {{ $item->quantity }}
                                                </div>
                                            </td>

                                            <td class="text-end">
                                                <strong>
                                                    Rp
                                                    {{ number_format($item->price_at_purchase * $item->quantity, 0, ',', '.') }}
                                                </strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <!-- ALAMAT -->
                        <h5>Alamat Pengiriman</h5>
                        <p>
                            {{ $order->customer_name }}<br>
                            {{ $order->customer_phone }}<br>
                            {{ $order->customer_address }}
                        </p>

                        <!-- CATATAN -->
                        @if($order->notes)
                            <h5>Catatan</h5>
                            <p class="alert alert-info">{{ $order->notes }}</p>
                        @endif

<<<<<<< HEAD
                        <div class="mt-4">
                            <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary">
                                Kembali ke Dashboard
                            </a>
=======
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary">
                                Kembali ke Dashboard
                            </a>
                            @if ($order->status == 'selesai')
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewOrderModal">
                                    Beri Ulasan
                                </button>
                            @endif
>>>>>>> hans
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
<<<<<<< HEAD
@endsection
=======

    <!-- Review Modal -->
    <div class="modal fade" id="reviewOrderModal" tabindex="-1" aria-labelledby="reviewOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewOrderModalLabel">Beri Ulasan untuk Pesanan #{{ $order->order_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('reviews.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @foreach ($order->items as $index => $item)
                            <div class="review-product-item mb-4">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('storage/' . optional($item->produk->fotoProduk->first())->file_foto_produk) }}" width="60" height="60" class="rounded me-3" style="object-fit:cover;">
                                    <h6>{{ $item->produk->nama_produk }}</h6>
                                </div>
                                <input type="hidden" name="reviews[{{ $index }}][produk_id]" value="{{ $item->produk_id }}">
                                <div class="mt-2">
                                    <label class="form-label">Rating Anda</label>
                                    <div class="star-rating-modal" data-review-index="{{ $index }}">
                                        <i class="fa fa-star-o" data-rating="1"></i>
                                        <i class="fa fa-star-o" data-rating="2"></i>
                                        <i class="fa fa-star-o" data-rating="3"></i>
                                        <i class="fa fa-star-o" data-rating="4"></i>
                                        <i class="fa fa-star-o" data-rating="5"></i>
                                    </div>
                                    <input type="hidden" name="reviews[{{ $index }}][rating]" id="rating-{{ $index }}" value="0" required>
                                </div>
                                <div class="mt-2">
                                    <label for="komentar-{{ $index }}" class="form-label">Komentar Anda</label>
                                    <textarea name="reviews[{{ $index }}][komentar]" id="komentar-{{ $index }}" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stars = document.querySelectorAll('.star-rating-modal .fa');

    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
            const rating = this.dataset.rating;
            const reviewIndex = this.parentElement.dataset.reviewIndex;
            highlightStars(reviewIndex, rating);
        });

        star.addEventListener('mouseout', function () {
            const reviewIndex = this.parentElement.dataset.reviewIndex;
            const currentRating = document.getElementById('rating-' + reviewIndex).value;
            highlightStars(reviewIndex, currentRating);
        });

        star.addEventListener('click', function () {
            const rating = this.dataset.rating;
            const reviewIndex = this.parentElement.dataset.reviewIndex;
            document.getElementById('rating-' + reviewIndex).value = rating;
        });
    });

    function highlightStars(reviewIndex, rating) {
        const starsInItem = document.querySelectorAll(`.star-rating-modal[data-review-index="${reviewIndex}"] .fa`);
        starsInItem.forEach(s => {
            if (s.dataset.rating <= rating) {
                s.classList.remove('fa-star-o');
                s.classList.add('fa-star');
            } else {
                s.classList.remove('fa-star');
                s.classList.add('fa-star-o');
            }
        });
    }
});
</script>
@endpush
>>>>>>> hans
