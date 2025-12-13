@extends('adminlte::page')


@section('title', 'Laporan Views Produk')

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

        #produkViewsChart {
            max-height: 360px;
        }
    </style>
@stop

@section('content_header')
    <h1 style="color:white; font-weight:600;">Laporan Views & Likes Produk</h1>
@stop

@section('content')

    {{-- RINGKASAN --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Produk</div>
                    <div class="metric-value">
                        {{ number_format($produks->count(), 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Produk yang tercatat views / likes
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Views</div>
                    <div class="metric-value">
                        {{ number_format($produks->sum('views'), 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Total klik / kunjungan detail produk
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Likes</div>
                    <div class="metric-value">
                        {{ number_format($produks->sum('likes'), 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Total produk disukai pengunjung
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD TABEL / GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size: 15px;">Performa Views & Likes per Produk</h3>

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
                                <th>Nama Produk</th>
                                <th class="text-right">Total Views</th>
                                <th class="text-right">Total Likes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($produks as $produk)
                                <tr>
                                    <td>{{ $produk->nama_produk }}</td>
                                    <td class="text-right">{{ number_format($produk->views, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($produk->likes, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center" style="opacity:.7; padding: 16px;">
                                        Belum ada data views / likes produk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- VIEW GRAFIK --}}
            <div id="view-chart" class="d-none">
                <canvas id="produkViewsChart"></canvas>
            </div>

        </div>

        @if ($produks->count())
            <div class="card-footer" style="font-size: 12px; opacity: .75;">
                <i class="fa fa-info-circle"></i>
                Produk dengan <strong>views</strong> tinggi menunjukkan minat pengunjung yang besar.
                Kombinasikan dengan <strong>likes</strong> untuk melihat mana yang benar-benar disukai.
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
            const canvas = document.getElementById('produkViewsChart');
            if (!canvas) return;

            const labels = {!! json_encode($produks->pluck('nama_produk')) !!};
            const views = {!! json_encode($produks->pluck('views')) !!};
            const likes = {!! json_encode($produks->pluck('likes')) !!};

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
                            label: 'Views',
                            data: views,
                            backgroundColor: 'rgba(90, 177, 247, 0.7)',
                            borderColor: 'rgba(90, 177, 247, 1)',
                            borderWidth: 1.5,
                            borderRadius: 5,
                        },
                        {
                            label: 'Likes',
                            data: likes,
                            backgroundColor: 'rgba(231, 76, 60, 0.7)',
                            borderColor: 'rgba(231, 76, 60, 1)',
                            borderWidth: 1.5,
                            borderRadius: 5,
                        }
                    ]
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
                                    return ' ' + value.toLocaleString('id-ID');
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
