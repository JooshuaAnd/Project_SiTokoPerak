@extends('guest.layouts.main')
@section('title', 'Checkout')

@section('content')
<div class="container py-5 mt-5">
    <h2 class="mb-4">Checkout</h2>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5>Informasi Pengiriman</h5>
                    <form action="{{ route('checkout.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label>Nama Lengkap</label>
                                <input type="text" name="name" class="form-control"
                                       value="{{ Auth::user()->name ?? old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label>No. HP / WhatsApp</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ Auth::user()->phone ?? old('phone') }}" required>
                            </div>
                            <div class="col-12">
                                <label>Alamat Lengkap</label>
                                <textarea name="address" rows="4" class="form-control" required>{{ Auth::user()->address ?? old('address') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label>Catatan (opsional)</label>
                                <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-check"></i> Buat Pesanan
                            </button>
                            <a href="{{ route('cart.index') }}" class="btn btn-secondary btn-lg">
                                Kembali ke Keranjang
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    @foreach($cart->items as $item)
                        <div class="d-flex justify-content-between py-2">
                            <span>{{ $item->produk->nama_produk }} × {{ $item->quantity }}</span>
                            <span>Rp {{ number_format($item->produk->harga * $item->quantity, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <strong>Pembayaran:</strong><br>
                Transfer ke BCA 1234567890 a.n. TekoPerakku<br>
                Konfirmasi otomatis via WhatsApp setelah transfer.
            </div>
        </div>
    </div>
</div>
@endsection
