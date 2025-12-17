@extends('pengerajin.layouts.pengerajin')

@section('title', 'Laporan Semua Transaksi')

{{-- ❌ Tidak perlu lagi @section("css") di sini, semua style sudah di layout --}}

@section('content_header')
    <h1>Laporan Semua Transaksi</h1>
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
            <div class="card-modern">
                <div class="metric-label">Total Transaksi</div>
                <div class="metric-value">
                    {{ number_format($totalTransaksi ?? 0, 0, ',', '.') }}
                </div>
                <span class="badge-soft mt-2">
                    Jumlah transaksi sesuai filter saat ini
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-modern">
                <div class="metric-label">Total Nominal</div>
                <div class="metric-value">
                    Rp {{ number_format($totalNominal ?? 0, 0, ',', '.') }}
                </div>
                <span class="badge-soft mt-2">
                    Akumulasi total transaksi (Rp) pada periode/filter
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-modern">
                <div class="metric-label">Rata-rata per Transaksi</div>
                <div class="metric-value">
                    @php
                        $avg = ($totalTransaksi ?? 0) > 0 ? $totalNominal / $totalTransaksi : 0;
                    @endphp
                    Rp {{ number_format($avg, 0, ',', '.') }}
                </div>
                <span class="badge-soft mt-2">
                    Rata-rata nilai transaksi sesuai filter
                </span>
            </div>
        </div>
    </div>

    {{-- 📋 TABEL TRANSAKSI --}}
    <div class="card-modern">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0" style="font-size: 15px;">Daftar Semua Transaksi</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-dark-custom table-striped table-hover mb-0 datatable">
                <thead>
                    <tr>
                        <th style="width:70px;">ID</th>
                        <th>User</th>
                        <th style="width:150px;" class="text-end">Total (Rp)</th>
                        <th style="width:180px;">Tanggal</th>
                        <th style="width:120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksi as $t)
                        <tr>
                            <td>#{{ $t->id }}</td>
                            <td>{{ $t->username }}</td>
                            <td class="text-end">
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
                    @endforeach
                </tbody>
            </table>

            {{-- Kalau mau info “tidak ada data”, taruh DI LUAR tbody saja --}}
            @if($transaksi->isEmpty())
                <div class="text-center text-muted py-3" style="font-size: 13px;">
                    Tidak ada transaksi untuk filter/periode ini.
                </div>
            @endif
        </div>
    </div>
@stop
