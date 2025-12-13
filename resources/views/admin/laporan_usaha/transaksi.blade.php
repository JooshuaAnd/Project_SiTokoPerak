@extends('adminlte::page')

@section('title', 'Laporan Semua Transaksi')

@section('css')
    <style>
        body {
            background: #0b1d39 !important;
        }

        .card-modern {
            background: #102544 !important;
            border-radius: 14px !important;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.35);
            color: #e8eef7;
        }

        .report-nav {
            background: #0f233f;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 18px;
        }

        .report-nav a {
            display: block;
            padding: 10px 14px;
            color: #b8ccdf;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 6px;
            text-decoration: none;
            transition: 0.2s;
        }

        .report-nav a:hover,
        .report-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-weight: 600;
        }

        .form-control,
        .btn {
            border-radius: 8px !important;
        }

        .form-control {
            background-color: #0b1d39;
            color: #e8eef7;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .table-dark-custom {
            background-color: #0f223f;
            color: #e8eef7;
        }

        .table-dark-custom thead tr {
            background-color: #081327;
        }

        .table-dark-custom tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04);
        }

        .metric-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            opacity: .75;
        }

        .metric-value {
            font-size: 22px;
            font-weight: 700;
            margin-top: 4px;
        }

        .badge-soft {
            background: rgba(90, 177, 247, 0.1);
            border: 1px solid rgba(90, 177, 247, 0.4);
            color: #5ab1f7;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .status-pending {
            background: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
            border: 1px solid rgba(241, 196, 15, 0.4);
        }

        .status-success {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.4);
        }

        .status-cancel {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.4);
        }
    </style>
@stop

@section('content_header')
    <h1 style="color:white; font-weight:600;">Laporan Semua Transaksi</h1>
@stop

@section('content')
    <div class="card-modern mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.laporan_usaha.transaksi') }}">
                <div class="row">
                    {{-- Usaha --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Usaha</label>
                        <select name="usaha_id" class="form-control">
                            <option value="">Semua Usaha</option>
                            @foreach ($usahaList as $usaha)
                                <option value="{{ $usaha->id }}"
                                    {{ (string) request('usaha_id') === (string) $usaha->id ? 'selected' : '' }}>
                                    {{ $usaha->nama_usaha }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kategori --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Kategori Produk</label>
                        <select name="kategori_id" class="form-control">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoriList as $kategori)
                                <option value="{{ $kategori->id }}"
                                    {{ (string) request('kategori_id') === (string) $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- User --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">User</label>
                        <select name="user_id" class="form-control">
                            <option value="">Semua User</option>
                            @foreach ($userList as $u)
                                <option value="{{ $u->id }}"
                                    {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                                    {{ $u->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            @foreach ($statusList as $s)
                                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-2">
                    {{-- Tanggal Mulai --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>

                {{-- ✅ Tambah filter periode --}}
                @include('admin.laporan_usaha.partials.filter_periode')

                <div class="row mt-2">
                    <div class="form-group col-md-3 col-sm-6" style="margin-top: 24px;">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fa fa-filter"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.laporan_usaha.transaksi') }}" class="btn btn-secondary btn-block">
                            <i class="fa fa-sync-alt"></i> Reset
                        </a>

                        <a href="{{ route('admin.laporan_usaha.transaksi.export', request()->query()) }}"
                            class="btn btn-success btn-block mt-2">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- 📊 RINGKASAN --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Transaksi</div>
                    <div class="metric-value">
                        {{ number_format($totalTransaksi ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Jumlah transaksi sesuai filter saat ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Nominal</div>
                    <div class="metric-value">
                        Rp {{ number_format($totalNominal ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Akumulasi total transaksi (Rp) pada periode/filter
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Rata-rata per Transaksi</div>
                    <div class="metric-value">
                        @php
                            $avg = ($totalTransaksi ?? 0) > 0 ? $totalNominal / $totalTransaksi : 0;
                        @endphp
                        Rp {{ number_format($avg, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Rata-rata nilai transaksi sesuai filter
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 📋 TABEL TRANSAKSI --}}
    <div class="card card-modern">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 15px;">Daftar Semua Transaksi</h3>
        </div>
        <div class="card-body" style="overflow-x:auto;">
            <table class="table table-dark-custom table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:70px;">ID</th>
                        <th>User</th>
                        <th style="width:150px;" class="text-right">Total (Rp)</th>
                        <th style="width:180px;">Tanggal</th>
                        <th style="width:120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $t)
                        <tr>
                            <td>#{{ $t->id }}</td>
                            <td>{{ $t->username }}</td>
                            <td class="text-right">
                                {{ number_format($t->total, 0, ',', '.') }}
                            </td>
                            <td>{{ $t->tanggal_transaksi }}</td>
                            <td>
                                @php
                                    $statusClass = 'status-badge status-' . strtolower($t->status ?? '');
                                @endphp
                                <span class="{{ $statusClass }}">
                                    {{ ucfirst($t->status ?? '-') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="opacity:.7; padding: 16px;">
                                Tidak ada transaksi untuk filter/periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop
