@extends('pengerajin.layouts.pengerajin')

@section('title', 'Laporan Kategori Produk')

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

        /* Toggle Tabel / Grafik */
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

        #kategoriChart {
            max-height: 340px;
        }
    </style>
@stop

@section('content_header')
    <h1 style="color:white; font-weight:600;">Laporan Kategori Produk</h1>
@stop

@section('content')

    {{-- 🔍 FILTER --}}
    <div class="card card-modern mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pengerajin.laporan_usaha.kategori-produk') }}">
                <div class="form-row">
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
                        <a href="{{ route('pengerajin.laporan_usaha.kategori-produk') }}" class="btn btn-secondary btn-block">
                            <i class="fa fa-sync-alt"></i> Reset
                        </a>

                        {{-- Optional tombol export --}}
                        <a href="{{ route('pengerajin.laporan_usaha.kategori-produk.export', request()->query()) }}"
                            class="btn btn-success btn-block mt-2">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- RINGKASAN --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Kategori</div>
                    <div class="metric-value">{{ number_format($laporan->count(), 0, ',', '.') }}</div>
                    <span class="badge badge-soft mt-2">Kategori aktif yang memiliki produk</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Produk</div>
                    <div class="metric-value">{{ number_format($laporan->sum('total_produk'), 0, ',', '.') }}</div>
                    <span class="badge badge-soft mt-2">Akumulasi semua produk per kategori</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Terjual</div>
                    <div class="metric-value">{{ number_format($laporan->sum('total_terjual'), 0, ',', '.') }}</div>
                    <span class="badge badge-soft mt-2">Quantity item terjual di semua kategori</span>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD TABEL / GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size: 15px;">Performa Kategori Produk</h3>

            {{-- Toggle Tabel / Grafik --}}
            <div class="toggle-pill" id="toggleView">
                <button type="button" class="active" data-view="table">Tabel</button>
                <button type="button" data-view="chart">Grafik</button>
            </div>
        </div>

        <div class="card-body" style="min-height: 320px;">

            {{-- VIEW TABEL --}}
            <div id="view-table">
                <div class="table-responsive">
                    <table class="table table-dark-custom table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Kategori</th>
                                <th class="text-right" style="width: 30%;">Total Produk</th>
                                <th class="text-right" style="width: 30%;">Total Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $l)
                                <tr>
                                    <td>{{ $l->nama_kategori_produk }}</td>
                                    <td class="text-right">{{ number_format($l->total_produk, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($l->total_terjual, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center" style="opacity:.7; padding: 16px;">
                                        Belum ada data kategori / transaksi untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- VIEW GRAFIK --}}
            <div id="view-chart" class="d-none">
                <canvas id="kategoriChart"></canvas>
            </div>

        </div>

        @if ($laporan->count())
            <div class="card-footer" style="font-size: 12px; opacity: .75;">
                <i class="fa fa-info-circle"></i>
                Kategori dengan <strong>Total Terjual</strong> tertinggi menunjukkan kelompok produk yang paling laris untuk
                filter yang dipilih.
            </div>
        @endif
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle Tabel / Grafik
        (function() {
            const toggle = document.getElementById('toggleView');
            if (!toggle) return;

            const btns = toggle.querySelectorAll('button');
            const viewTable = document.getElementById('view-table');
            const viewChart = document.getElementById('view-chart');

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

        // CHART
        (function() {
            const canvas = document.getElementById('kategoriChart');
            if (!canvas) return;

            const labels = {!! json_encode($laporan->pluck('nama_kategori_produk')) !!};
            const data = {!! json_encode($laporan->pluck('total_terjual')) !!};

            if (!labels.length) {
                canvas.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 40px;">Tidak ada data untuk ditampilkan.</p>';
                return;
            }

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Terjual',
                        data: data,
                        backgroundColor: 'rgba(90, 177, 247, 0.6)',
                        borderColor: 'rgba(90, 177, 247, 1)',
                        borderWidth: 1.5,
                        borderRadius: 6,
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
