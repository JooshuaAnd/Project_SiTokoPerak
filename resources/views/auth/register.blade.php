@extends('guest.layouts.main')
@section('title', 'Daftar Akun')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/login-style.css') }}">
@endpush

@section('content')
    <div class="login-page-wrapper">
        <div class="login-card">
            <h2 class="mb-4">Daftar Akun Baru</h2>

            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}" required>
                    @error('username') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required>
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>No. HP (opsional)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>

                <div class="form-group mb-3">
                    <label>Alamat (opsional)</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                </div>

                <div class="form-group mb-3">
                    <label>Kata Sandi</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password"
                            class="form-control @error('password') is-invalid @enderror" required>
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                            <i class="fa fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-4">
                    <label>Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn-login w-100">Daftar</button>

                <div class="text-center mt-3">
                    <span class="text-muted">Sudah punya akun?
                        <a href="{{ route('login') }}" class="link-primary">Masuk di sini</a>
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
                toggleIcon.classList.toggle('fa-eye');
                toggleIcon.classList.toggle('fa-eye-slash');
            });
        }
    </script>
@endpush