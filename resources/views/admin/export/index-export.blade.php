@extends('adminlte::page')

@section('title', 'Foto Produk')

@section('content_header')
    <style>
        body {
            background: #0b1d39 !important;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
        }

        .card-modern {
            background: #102544 !important;
            border-radius: 14px !important;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
            color: #e8eef7;
            padding: 18px;
            margin-bottom: 16px;
        }

        .metric-value {
            font-size: 30px;
            font-weight: 700;
            margin-top: 5px;
            color: #5ab1f7;
        }

        .metric-label {
            font-size: 13px;
            opacity: .8;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .chart-box {
            height: 260px;
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

        h5 {
            font-size: 15px;
            margin-bottom: 8px;
        }
    </style>
@stop
@section('content')

    <div class="report-nav">
        <a href="{{ route('admin.laporan_usaha.index') }}" class="active">📌 Dashboard Laporan</a>
        <a href="{{ route('admin.laporan_usaha.transaksi') }}">📄 Semua Transaksi</a>
        <a href="{{ route('admin.laporan_usaha.pendapatan-usaha') }}">💰 Pendapatan Per Usaha</a>
        <a href="{{ route('admin.laporan_usaha.produk_terlaris') }}">🔥 Produk Terlaris</a>
        <a href="{{ route('admin.laporan_usaha.produk-slow-moving') }}">🐌 Produk Slow Moving</a>
        <a href="{{ route('admin.laporan_usaha.transaksi-user') }}">👥 Transaksi Per User</a>
        <a href="{{ route('admin.laporan_usaha.kategori-produk') }}">📦 Kategori Produk</a>
        <a href="{{ route('admin.laporan_usaha.produk-favorite') }}">❤️ Produk Favorite</a>
        <a href="{{ route('admin.laporan_usaha.produk-views') }}">👁️ Produk Dilihat</a>
    </div>

    {{-- <a href="{{ route('admin.export-pengerajin') }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel"></i> Export Data Pengerajin</a> --}}
    {{-- tambah jarak dan garis --}}
    <br>
    {{-- tambah jarak dan garis --}}
    <hr color="#ccc">
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/custom.css"> --}}
@stop

@section('js')
    {{-- <script src="/js/custom.js"></script> --}}

@stop
