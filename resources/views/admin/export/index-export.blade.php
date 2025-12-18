@extends('adminlte::page')

@section('title', 'Foto Produk')


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
