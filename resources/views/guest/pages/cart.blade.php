@extends('guest.layouts.main')
@section('title', 'Keranjang Belanja')

{{-- Auto Update Script --}}
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            document.querySelectorAll(".auto-update-form").forEach(function (form) {

                let input = form.querySelector(".qty-input");
                let minusBtn = form.querySelector(".minus");
                let plusBtn = form.querySelector(".plus");

                minusBtn.addEventListener("click", function () {
                    let val = parseInt(input.value);
                    if (val > 1) {
                        input.value = val - 1;
                        form.submit();
                    }
                });

                plusBtn.addEventListener("click", function () {
                    input.value = parseInt(input.value) + 1;
                    form.submit();
                });

                input.addEventListener("change", function () {
                    if (input.value < 1) input.value = 1;
                    form.submit();
                });

            });

        });
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/cart.css') }}">
@endpush

@section('content')
    <div class="container py-5 mt-5">
        <h2 class="mb-4">Keranjang Belanja</h2>


        {{-- ==========================
        MODE: USER LOGIN (DB)
        =========================== --}}
        @if(isset($cartDB) && $cartDB->items->count() > 0)

            <div class="table-responsive">
                <table class="table align-middle cart-table">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($cartDB->items as $item)
                            <tr>
                                {{-- PRODUK --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->produk->fotoProduk->first())
                                            <img src="{{ asset('storage/' . $item->produk->fotoProduk->first()->file_foto_produk) }}"
                                                width="70" height="70" class="rounded me-3" style="object-fit:cover;">
                                        @endif
                                        <strong>{{ $item->produk->nama_produk }}</strong>
                                    </div>
                                </td>

                                {{-- HARGA --}}
                                <td>Rp {{ number_format($item->produk->harga, 0, ',', '.') }}</td>

                                {{-- UPDATE JUMLAH --}}
                                <td>
                                    <form action="{{ route('cart.update') }}" method="POST"
                                        class="d-flex align-items-center gap-2 auto-update-form">
                                        @csrf

                                        <div class="qty-container d-flex align-items-center">
                                            <button type="button" class="qty-btn minus">-</button>

                                            <input type="number" name="quantity[{{ $item->id }}]" value="{{ $item->quantity }}"
                                                class="qty-input" min="1">

                                            <button type="button" class="qty-btn plus">+</button>
                                        </div>
                                    </form>
                                </td>

                                {{-- SUBTOTAL --}}
                                <td class="fw-bold">
                                    Rp {{ number_format($item->produk->harga * $item->quantity, 0, ',', '.') }}
                                </td>

                                {{-- REMOVE --}}
                                <td>
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TOTAL + BUTTON --}}
            {{-- TOTAL UNTUK USER LOGIN --}}
            <div class="text-end mt-4 border-top pt-3">
                <h4 class="fw-bold mb-3">
                    Total:
                    <span class="text-success">
                        Rp {{ number_format($cartDB->items->sum(fn($i) => $i->produk->harga * $i->quantity), 0, ',', '.') }}
                    </span>
                </h4>

                <a href="{{ route('checkout.index') }}" class="btn btn-success btn-lg px-5">
                    Checkout <i class="fa fa-arrow-right ms-2"></i>
                </a>
            </div>

            {{-- clear cart --}}
            <form action="{{ route('cart.clear') }}" method="POST" class="mt-3">
                @csrf
                <button class="btn btn-outline-danger btn-sm">
                    <i class="fa fa-trash"></i> Kosongkan Keranjang
                </button>
            </form>




            {{-- ==========================
            MODE: GUEST SESSION CART
            =========================== --}}


        @elseif(isset($sessionCart) && count($sessionCart) > 0)
            <div class="table-responsive">
                <table class="table align-middle cart-table">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($sessionCart as $id => $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item['foto'])
                                            <img src="{{ asset('storage/' . $item['foto']) }}" width="70" height="70"
                                                class="rounded me-3" style="object-fit:cover;">
                                        @endif
                                        <strong>{{ $item['nama_produk'] }}</strong>
                                    </div>
                                </td>

                                <td>Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>

                                <td>
                                    <form action="{{ route('cart.update') }}" method="POST"
                                        class="d-flex align-items-center gap-2 auto-update-form">
                                        @csrf

                                        <div class="qty-container d-flex align-items-center">
                                            <button type="button" class="qty-btn minus">-</button>

                                            <input type="number" name="quantity[{{ $id }}]" value="{{ $item['quantity'] }}"
                                                class="qty-input" min="1">

                                            <button type="button" class="qty-btn plus">+</button>
                                        </div>
                                    </form>
                                </td>

                                <td class="fw-bold">
                                    Rp {{ number_format($item['harga'] * $item['quantity'], 0, ',', '.') }}
                                </td>

                                <td>
                                    <form action="{{ route('cart.remove', $id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TOTAL UNTUK GUEST --}}
            <div class="text-end mt-4 border-top pt-3">
                <h4 class="fw-bold mb-3">
                    Total:
                    <span class="text-success">
                        Rp {{ number_format(collect($sessionCart)->sum(fn($i) => $i['harga'] * $i['quantity']), 0, ',', '.') }}
                    </span>
                </h4>

                <a href="#" class="btn btn-success btn-lg px-5">
                    Checkout <i class="fa fa-arrow-right ms-2"></i>
                </a>
            </div>

            {{-- clear cart --}}
            <form action="{{ route('cart.clear') }}" method="POST" class="mt-3">
                @csrf
                <button class="btn btn-outline-danger btn-sm">
                    <i class="fa fa-trash"></i> Kosongkan Keranjang
                </button>
            </form>




            {{-- ==========================
            EMPTY CART
            =========================== --}}
        @else
            <div class="text-center py-5">
                <i class="fa fa-shopping-cart fa-5x text-muted"></i>
                <h4>Keranjang kosong</h4>
                <a href="{{ route('guest-katalog') }}" class="btn btn-primary mt-3">Belanja Sekarang</a>
            </div>
        @endif

    </div>
@endsection
