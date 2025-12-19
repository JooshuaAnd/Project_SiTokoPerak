@extends('adminlte::page')

@section('title', 'Laporan Semua Transaksi')

@section('content')
    @include('admin.laporan_usaha.filter', [
        'action' => route('admin.laporan_usaha.transaksi'),
        'resetUrl' => route('admin.laporan_usaha.transaksi'),
        'showUsaha' => true,
        'showKategori' => true,
        'showStatus' => true,
        'showPengerajin' => true,
        'showDateRange' => true,
        'showPeriode' => true,
        'exportRoute' => 'admin.laporan_usaha.transaksi.export',
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
