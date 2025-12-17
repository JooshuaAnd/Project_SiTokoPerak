@extends('pengerajin.layouts.pengerajin')

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
    @include('pengerajin.laporan_usaha.filter', [
        'action' => route('pengerajin.laporan_usaha.transaksi'),
        'resetUrl' => route('pengerajin.laporan_usaha.transaksi'),
        'showUsaha' => true,
        'showKategori' => true,
        'showStatus' => true,
        'showDateRange' => true,
        'showPeriode' => true,
        'exportRoute' => 'pengerajin.laporan_usaha.transaksi.export',
    ])
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
