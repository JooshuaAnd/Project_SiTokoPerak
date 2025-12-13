@extends('adminlte::page')

@section('title', 'Profil Pengguna')

@section('content_header')
    <h1>Profil Pengguna</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-9 col-lg-7">
            <div class="card">
                <div class="card-body">

                    {{-- Bagian Avatar --}}
                    <div class="text-center mb-4">
                        @php
                            // Ambil 2 huruf pertama dari nama untuk inisial
                            $user = Auth::user();
                            // Pecah nama menjadi beberapa bagian berdasarkan spasi
                            $nameParts = explode(' ', $user->name);
                            // Ambil huruf pertama dari kata pertama
                            $firstInitial = strtoupper(substr($nameParts[0], 0, 1));
                            // Ambil huruf pertama dari kata terakhir (aman untuk nama 1 kata)
                            $lastInitial = strtoupper(substr(end($nameParts), 0, 1));
                            // Gabungkan inisialnya
                            $initials = $firstInitial . $lastInitial;
                        @endphp
                        <div class="profile-avatar-circle">
                            <span>{{ $initials }}</span>
                        </div>
                    </div>

                    {{-- Form Informasi Pengguna --}}
                    <form>
                        <div class="form-group">
                            <label for="name">Nama</label>
                            <input type="text" id="name" class="form-control" value="{{ $user->username }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" class="form-control" value="{{ $user->username }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" class="form-control" value="{{ $user->email }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <input type="text" id="role" class="form-control" value="{{ ucfirst($user->role ?? 'User') }}" readonly>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Tambahkan CSS kustom di sini --}}
    <style>
        .profile-avatar-circle {
            width: 80px;
            height: 80px;
            background-color: #6c757d; /* Warna abu-abu AdminLTE */
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto;
        }

        .form-control[readonly] {
            background-color: #e9ecef; /* Warna latar input readonly */
            opacity: 1;
            cursor: default;
        }
    </style>
@stop

@section('js')
    {{-- JavaScript Anda bisa diletakkan di sini jika perlu --}}
@stop