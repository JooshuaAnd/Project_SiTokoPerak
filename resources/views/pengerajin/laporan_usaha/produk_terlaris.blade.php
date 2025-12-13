@extends('pengerajin.layouts.pengerajin')

@section('title', 'Laporan Produk Terlaris')

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

        .toggle-pill {
            display: inline-flex;
            background: #0b1d39;
            border-radius: 999px;
            padding: 2px;
        }

        .toggle-pill button {
            border: none;
            background: transparent;
            color: #b8ccdf;
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 999px;
            outline: none;
            cursor: pointer;
            transition: all .18s ease;
        }

        .toggle-pill button.active {
            background: #1f3f72;
            color: #ffffff;
            font-weight: 600;
        }

        #produkTerlarisChart {
            max-height: 360px;
        }
    </style>
@stop

@section('content_header')
    <h1 style="color:white; font-weight:600;">Laporan Produk Terlaris</h1>
@stop

@section('content')
    <div class="card-modern mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pengerajin.laporan_usaha.produk-terlaris') }}">
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

                    {{-- Kategori Produk --}}
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
                @include('pengerajin.laporan_usaha.partials.filter_periode')

                <div class="row mt-2">
                    <div class="form-group col-md-3 col-sm-6" style="margin-top: 8px;">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fa fa-filter"></i> Terapkan
                        </button>
                        <a href="{{ route('pengerajin.laporan_usaha.produk-terlaris') }}" class="btn btn-secondary btn-block">
                            <i class="fa fa-sync-alt"></i> Reset
                        </a>

                        <a href="{{ route('pengerajin.laporan_usaha.produk-terlaris.export', request()->query()) }}"
                            class="btn btn-success btn-block mt-2">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 📊 RINGKASAN --}}
    @php
        $topNama = $topRow->nama_produk ?? '-';
        $topQty = $topRow->total_terjual ?? 0;
    @endphp

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Produk Terjual (Distinct)</div>
                    <div class="metric-value">
                        {{ number_format($totalProduk, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Jumlah produk yang muncul dalam laporan ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Kuantitas Terjual</div>
                    <div class="metric-value">
                        {{ number_format($totalTerjual, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Akumulasi jumlah item yang terjual dalam periode/filter
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Produk Paling Laris</div>
                    <div class="metric-value">
                        {{ $topNama }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Terjual {{ number_format($topQty, 0, ',', '.') }} item
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 📋 CARD TABEL / GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size: 15px;">Performa Produk Terlaris</h3>

            <div class="toggle-pill" id="toggleViewTerlaris">
                <button type="button" class="active" data-view="table">Tabel</button>
                <button type="button" data-view="chart">Grafik</button>
            </div>
        </div>

        <div class="card-body" style="min-height: 320px;">

            {{-- VIEW TABEL --}}
            <div id="view-terlaris-table">
                <div class="table-responsive">
                    <table class="table table-dark-custom table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Nama Produk</th>
                                <th class="text-right" style="width:150px;">Total Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->nama_produk }}</td>
                                    <td class="text-right">
                                        {{ number_format($row->total_terjual, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center" style="opacity:.7; padding: 16px;">
                                        Tidak ada data produk terlaris untuk filter/periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- VIEW GRAFIK --}}
            <div id="view-terlaris-chart" class="d-none">
                <canvas id="produkTerlarisChart"></canvas>
            </div>

        </div>

        @if ($totalTerjual > 0)
            <div class="card-footer" style="font-size: 12px; opacity: .75;">
                <i class="fa fa-info-circle"></i>
                Fokuskan stok & promosi pada produk dengan <strong>penjualan tertinggi</strong>.
                Pertimbangkan juga paket bundling dengan produk lain yang penjualannya rendah.
            </div>
        @endif
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle Tabel / Grafik
        (function() {
            const toggle = document.getElementById('toggleViewTerlaris');
            if (!toggle) return;

            const btns = toggle.querySelectorAll('button');
            const viewTable = document.getElementById('view-terlaris-table');
            const viewChart = document.getElementById('view-terlaris-chart');

            btns.forEach(btn => {
                btn.addEventListener('click', function() {
                    btns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const view = this.getAttribute('data-view');
                    if (view === 'chart') {
                        viewTable.classList.add('d-none');
                        viewChart.classList.remove('d-none');
                    } else {
                        viewChart.classList.add('d-none');
                        viewTable.classList.remove('d-none');
                    }
                });
            });
        })();

        // CHART Produk Terlaris (pakai data top 10 dari controller)
        (function() {
            const canvas = document.getElementById('produkTerlarisChart');
            if (!canvas) return;

            const chartData = @json($chartData);

            if (!chartData.length) {
                canvas.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 40px;">Tidak ada data untuk ditampilkan.</p>';
                return;
            }

            const labels = chartData.map(r => r.nama_produk);
            const qty = chartData.map(r => Number(r.total_terjual));

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Terjual',
                        data: qty,
                        backgroundColor: 'rgba(255, 159, 64, 0.8)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1.5,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#b8ccdf'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y ?? 0;
                                    return ' ' + value.toLocaleString('id-ID') + ' item';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#b8ccdf'
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#b8ccdf',
                                callback: function(value) {
                                    return value.toLocaleString('id-ID');
                                }
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.06)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        })();
    </script>
@stop
