<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900&display=swap"
        rel="stylesheet">

    <title>Toko Perak Kotagedhe</title>


    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/templatemo-hexashop.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl-carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lightbox.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet" />


    <link rel="stylesheet" href="{{ asset('assets/css/index-css.css') }}">
    @stack('styles')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll("form").forEach(form => {
                form.addEventListener("submit", function (e) {
                    if (form.dataset.submitted === "true") {
                        e.preventDefault();
                        return false;
                    }
                    form.dataset.submitted = "true";
                });
            });
        });
    </script>



</head>

<body>
    @if (session('success'))
        <div class="custom-alert-toast">
            {{ session('success') }}
        </div>
    @endif
    <!-- ***** Preloader Start ***** -->
    <div id="preloader">
        <div class="jumper">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    <!-- ***** Preloader End ***** -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const alertToast = document.querySelector('.custom-alert-toast');
            if (alertToast) {
                setTimeout(() => {
                    alertToast.style.opacity = '0';
                    setTimeout(() => alertToast.style.display = 'none', 500);
                }, 5000); // Hide after 5 seconds
            }
        });
    </script>
    <!-- ***** Header Area Start ***** -->
    <header class="shadow-sm fixed-top">
        <nav class="navbar navbar-expand-lg bg-white">
            <div class="container d-flex align-items-center">

                <!-- Logo -->
                <a class="navbar-brand fw-bold fs-4 me-3" href="{{ route('guest-index') }}">
                    TekoPerakku
                </a>

                <!-- Search -->
                <form action="{{ route('guest-katalog') }}" method="GET" class="d-flex flex-grow-1 mx-4">
                    <input class="form-control" type="search" name="search" placeholder="Cari produk atau kategori..."
                        value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-secondary ms-2">
                        <i class="fa fa-search"></i>
                    </button>
                </form>



                <!-- Right Icons: Cart + Login/Logout -->
                <div class="d-flex align-items-center gap-4">

                    <!-- CART ICON -->
                    <a href="{{ route('cart.index') }}"
                        class="text-dark text-decoration-none position-relative d-inline-flex align-items-center">

                        <i class="fa fa-shopping-cart fa-lg"></i>

                        <!-- Badge -->
                        <span class="badge bg-danger rounded-pill position-absolute cart-badge">
                            {{ cart_count() }}
                        </span>
                    </a>










                    <!-- LOGIN / USER MENU -->
                    @auth
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                                id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fa fa-user me-2"></i>
                                <span class="d-none d-sm-inline">{{ Auth::user()->name ?? Auth::user()->username }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.dashboard') }}">
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.dashboard') }}#orders">
                                        Pesanan Saya
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-dark d-flex align-items-center text-decoration-none">
                            <i class="fa fa-user me-2"></i>
                            <span class="d-none d-sm-inline">Login</span>
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Menu Bawah (Kategori, dll) tetap sama -->
        <div class="bg-white border-top">
            <div class="container">
                <ul class="nav justify-content-center py-2">
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('guest-index') ? 'active' : '' }}"
                            href="{{ route('guest-index') }}">BERANDA</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('guest-katalog') ? 'active' : '' }}"
                            href="{{ route('guest-katalog') }}">KATALOG</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="kategoriDropdown" role="button"
                            data-bs-toggle="dropdown">
                            KATEGORI
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="kategoriDropdown">
                            @foreach ($kategoris as $kategori)
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('guest-katalog', ['kategori' => $kategori->slug]) }}">
                                        {{ $kategori->nama_kategori_produk }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('guest-about') ? 'active' : '' }}"
                            href="{{ route('guest-about') }}">TENTANG KAMI</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('guest-contact') ? 'active' : '' }}"
                            href="{{ route('guest-contact') }}">KONTAK</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>




    <!-- ***** Content Start ***** -->
    <div class="content">
        @yield('content') <!-- Tempat untuk menampilkan content dinamis -->
    </div>

    <!-- <div class="sticky-checkout-bar shadow-lg">
        <div class="container d-flex justify-content-between align-items-center py-2">
            <strong>Total: Rp
                {{ number_format(collect(session('cart'))->sum(fn($i) => $i['harga'] * $i['quantity']), 0, ',', '.') }}
            </strong>

            <a href="#" class="btn btn-success">
                Checkout
            </a>
        </div>
    </div> -->


    <!-- ***** Content End ***** -->



    <!-- ***** Footer Start ***** -->
    {{-- Kode Footer Baru dengan Grid Bootstrap --}}
    <footer class="footer">
        <div class="container">
            <div class="row">

                {{-- Kolom 1: Logo & Alamat (Lebih besar) --}}
                <div class="col-lg-4 col-md-6 mb-4">
                    <h3 class="footer-logo">TekoPerakku</h3>
                    <ul class="footer-list">
                        <li>59GX+957, JL. Watu Gateng,<br>Prenggan, Kec. Kotagede, Kota Yogyakarta</li>
                        <li>kotagedhe@gmail.com</li>
                        <li>088-098-202</li>
                    </ul>
                </div>

                {{-- Kolom 2: Kategori --}}
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Kategori</h5>
                    <ul class="footer-list">
                        @foreach ($randomKategoris->take(4) as $kategori) {{-- Batasi 4 item --}}
                            <li>
                                <a href="#">{{-- Ganti dengan route --}}
                                    {{ $kategori->nama_kategori_produk }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Kolom 3: Informasi Kami --}}
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Informasi Kami</h5>
                    <ul class="footer-list">
                        <li><a href="{{ route('guest-index') }}">Beranda</a></li>
                        <li><a href="{{ route('guest-about') }}">Tentang Kami</a></li>
                        <li><a href="{{ route('guest-contact') }}">Kontak Kami</a></li>
                    </ul>
                </div>

                {{-- Kolom 4: Sosial Media --}}
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Sosial Media</h5>
                    <div class="footer-social">
                        <a href="#"><i class="fa fa-facebook"></i></a>
                        <a href="#"><i class="fa fa-twitter"></i></a>
                        <a href="#"><i class="fa fa-instagram"></i></a>
                    </div>
                </div>

            </div>

            {{-- Copyright --}}
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p class="footer-copy">Copyright © 2025 | All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>


    <!-- jQuery -->
    <script src="{{ asset('assets/js/jquery-2.1.0.min.js') }}"></script>

    <!-- Bootstrap -->
    @stack('scripts')
    <script src="{{ asset('assets/js/popper.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>

    <!-- Plugins -->
    <script src="{{ asset('assets/js/owl-carousel.js') }}"></script>
    <script src="{{ asset('assets/js/accordions.js') }}"></script>
    <script src="{{ asset('assets/js/datepicker.js') }}"></script>
    <script src="{{ asset('assets/js/scrollreveal.min.js') }}"></script>
    <script src="{{ asset('assets/js/waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('assets/js/imgfix.min.js') }}"></script>
    <script src="{{ asset('assets/js/slick.js') }}"></script>
    <script src="{{ asset('assets/js/lightbox.js') }}"></script>
    <script src="{{ asset('assets/js/isotope.js') }}"></script>

    <!-- Global Init -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script>
        $(function () {
            var selectedClass = "";
            $("p").click(function () {
                selectedClass = $(this).attr("data-rel");
                $("#portfolio").fadeTo(50, 0.1);
                $("#portfolio div").not("." + selectedClass).fadeOut();
                setTimeout(function () {
                    $("." + selectedClass).fadeIn();
                    $("#portfolio").fadeTo(50, 1);
                }, 500);
            });
        });
    </script>




</body>

</html>
