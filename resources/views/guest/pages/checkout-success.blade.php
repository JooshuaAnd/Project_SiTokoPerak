@extends('guest.layouts.main')
@section('title', 'Pesanan Berhasil!')

@section('content')
<div class="container py-5 mt-5 text-center">
    <i class="fa fa-check-circle fa-5x text-success mb-4"></i>
    <h2>Pesanan Berhasil Dibuat!</h2>
    <p class="lead">Nomor Pesanan: <strong>{{ $order->order_number }}</strong></p>
    <p>Total: <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></p>

    <div class="my-4">
        <a href="https://wa.me/6281234567890?text=Halo,%20saya%20mau%20konfirmasi%20pembayaran%20pesanan%20{{ $order->order_number }}"
           class="btn btn-success btn-lg" target="_blank">
            <i class="fa fa-whatsapp"></i> Konfirmasi via WhatsApp
        </a>
    </div>

    <a href="{{ route('guest-index') }}" class="btn btn-primary">Kembali Belanja</a>
</div>
@endsection


