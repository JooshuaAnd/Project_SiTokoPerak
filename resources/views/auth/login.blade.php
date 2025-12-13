@extends('guest.layouts.main')
@section('title', 'Masuk')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/login-style.css') }}">
@endpush

@section('content')
    <div class="login-page-wrapper">
        <div class="login-card">
            <h2 class="mb-4">Masuk</h2>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="masukkan Username"
                        required />
                </div>

                <div class="form-group mb-3">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="masukkan kata Sandi" required />
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                            <i class="fa fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            Ingat saya
                        </label>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <button type="submit" class="btn-login w-100">Masuk</button>

                <div class="text-center mt-3">
                    <span class="text-muted">Lupa kata sandi?
                        <a href="#" class="link-primary">Ubah kata sandi</a>
                    </span>
                </div>

                <div class="text-center mt-3">
                    <span class="text-muted">Belum punya akun?
                        <a href="{{ route('register') }}" class="link-primary">Daftar di sini</a>
                    </span>
                </div>

                
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (togglePassword) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Ganti iconnya
                toggleIcon.classList.toggle('fa-eye');
                toggleIcon.classList.toggle('fa-eye-slash');
            });
        }
    </script>
@endpush
