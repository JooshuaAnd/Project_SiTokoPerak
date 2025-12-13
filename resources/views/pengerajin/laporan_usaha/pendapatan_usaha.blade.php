@extends('pengerajin.layouts.pengerajin')


@section('title', 'Pendapatan Usaha')

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
            background: rgba(90, 177, 247, 0.12);
            border: 1px solid rgba(90, 177, 247, 0.4);
            color: #5ab1f7;
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

        #pendapatanUsahaChart {
            max-height: 380px;
        }
    </style>
@stop

@section('content_header')
    <h1 style="color:black; font-weight:600;">Laporan Pendapatan Per Usaha</h1>
@stop

@section('content')
    {{-- 🔍 FILTER --}}
    <div class="card-modern mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pengerajin.laporan_usaha.pendapatan-usaha') }}">
                <div class="row">
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

                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>

                {{-- ✅ Tambah filter periode --}}
                @include('pengerajin.laporan_usaha.partials.filter_periode')

                <div class="row mt-2">
                    <div class="form-group col-md-3 col-sm-6" style="margin-top: 4px;">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fa fa-filter"></i> Terapkan
                        </button>
                        <a href="{{ route('pengerajin.laporan_usaha.pendapatan-usaha') }}" class="btn btn-secondary btn-block">
                            <i class="fa fa-sync-alt"></i> Reset
                        </a>

                        <a href="{{ route('pengerajin.laporan_usaha.pendapatan-usaha.export', request()->query()) }}"
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
        <div class="col-md-3">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Usaha</div>
                    <div class="metric-value">
                        {{ number_format($totalUsaha ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Banyak Usaha
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Transaksi</div>
                    <div class="metric-value">
                        {{ number_format($totalTransaksi ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Akumulasi transaksi
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Pendapatan</div>
                    <div class="metric-value">
                        Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Jumlah penjualan (subtotal detail)
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Rata-rata Nilai Transaksi</div>
                    <div class="metric-value">
                        Rp {{ number_format($avgTransaksiGlobal ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Rata-rata nilai 1 transaksi
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 📋 CARD TABEL / GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size: 15px;">Performa Pendapatan Per Usaha</h3>

            <div class="toggle-pill" id="togglePendapatanUsaha">
                <button type="button" class="active" data-view="table">Tabel</button>
                <button type="button" data-view="chart">Grafik</button>
            </div>
        </div>

        <div class="card-body" style="min-height: 340px;">

            {{-- TABEL --}}
            <div id="view-pendapatan-table">
                <div class="table-responsive">
                    <table class="table table-dark-custom table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usaha</th>
                                <th class="text-right">Total Transaksi</th>
                                <th class="text-right">Total Penjualan</th>
                                <th class="text-right">Rata-rata Transaksi</th>
                                <th class="text-center">Transaksi Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->nama_usaha }}</td>
                                    <td class="text-right">
                                        {{ number_format($row->total_transaksi, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        Rp {{ number_format($row->rata_rata_transaksi, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($row->transaksi_terakhir)->format('d-m-Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center" style="opacity:.7; padding: 16px;">
                                        Tidak ada data pendapatan usaha untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- GRAFIK --}}
            <div id="view-pendapatan-chart" class="d-none">
                <canvas id="pendapatanUsahaChart"></canvas>
            </div>

        </div>

        @if (($laporan ?? collect())->count() > 0)
            <div class="card-footer" style="font-size: 12px; opacity:.75;">
                <i class="fa fa-info-circle"></i>
                Usaha dengan <strong>total penjualan tertinggi</strong> bisa menjadi prioritas untuk
                stok, promosi, atau pengembangan produk lebih lanjut.
            </div>
        @endif
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle Tabel / Grafik
        (function() {
            const toggle = document.getElementById('togglePendapatanUsaha');
            if (!toggle) return;

            const btns = toggle.querySelectorAll('button');
            const viewTable = document.getElementById('view-pendapatan-table');
            const viewChart = document.getElementById('view-pendapatan-chart');

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

        // CHART Pendapatan per Usaha
        (function() {
            const canvas = document.getElementById('pendapatanUsahaChart');
            if (!canvas) return;

            const raw = @json($laporan);

            if (!raw.length) {
                canvas.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 40px;">Tidak ada data untuk ditampilkan.</p>';
                return;
            }

            // Ambil maksimal 10 usaha teratas biar grafik tidak terlalu padat
            const top = raw.slice(0, 10);

            const labels = top.map(r => r.nama_usaha);
            const values = top.map(r => Number(r.total_penjualan));

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Penjualan (Rp)',
                        data: values,
                        backgroundColor: 'rgba(90, 177, 247, 0.7)',
                        borderColor: 'rgba(90, 177, 247, 1)',
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
                                    const val = context.parsed.y ?? 0;
                                    return ' Rp ' + val.toLocaleString('id-ID');
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
                                    return 'Rp ' + value.toLocaleString('id-ID');
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
