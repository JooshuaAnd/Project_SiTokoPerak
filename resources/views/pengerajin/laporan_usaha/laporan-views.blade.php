@extends('pengerajin.layouts.pengerajin')
@section('title', 'Dashboard Laporan')

@section('css')
    <style>
        /* Mengadopsi style AdminLTE agar mirip dengan skema warna gelap Anda */
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

        .form-control {
            background-color: #0b1d39;
            color: #e8eef7;
            border-color: rgba(255, 255, 255, 0.1);
        }

        h5 {
            font-size: 15px;
            margin-bottom: 8px;
            color: #e8eef7;
        }
    </style>
@stop

@section('content')

    {{-- HEADER DAN NAVIGASI --}}
    <h1 style="color:white; font-weight:600;">Dashboard Laporan</h1>
    <hr style="color:#b8ccdf;">


    {{-- 🔍 FILTER GLOBAL (Card yang membungkus semua filter) --}}
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
    {{-- GRID DASHBOARD UTAMA --}}
    <div class="dashboard-grid">

        {{-- METRIC 1: TOTAL TRANSAKSI --}}
        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">Total Transaksi</div>
            <div class="metric-value">{{ number_format($totalTransaksi ?? 0, 0, ',', '.') }}</div>
        </div>

        {{-- METRIC 2: TOTAL PENDAPATAN --}}
        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">Total Pendapatan</div>
            <div class="metric-value">
                Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}
            </div>
        </div>

        {{-- METRIC 3: PRODUK TERLARIS --}}
        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">Produk Terlaris (Penjualan)</div>
            <div class="metric-value" style="font-size: 22px;">
                {{ $topProduk ?? 'N/A' }}
            </div>
        </div>

        {{-- METRIC 4: USER AKTIF TERTINGGI --}}
        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">User Aktif Tertinggi</div>
            <div class="metric-value" style="font-size: 22px;">
                {{ $userAktif ?? 'N/A' }}
            </div>
        </div>

        {{-- CHART 1: PENDAPATAN PER USAHA (Span 6) --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>💰 Pendapatan Top 3 Usaha (Yang Anda kelola)</h5>
            <div class="chart-box"><canvas id="chartPendapatan"></canvas></div>
        </div>

        {{-- CHART 2: TOP KATEGORI (Span 6) --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>📦 Top 3 Kategori Produk (Total Terjual)</h5>
            <div class="chart-box"><canvas id="chartKategori"></canvas></div>
        </div>

        {{-- CHART 3: PRODUK TERLARIS (Span 4) --}}
        <div class="card-modern" style="grid-column: span 4;">
            <h5>🔥 Top 3 Produk Terlaris (Penjualan)</h5>
            <div class="chart-box"><canvas id="chartTerlaris"></canvas></div>
        </div>

        {{-- CHART 4: PRODUK FAVORITE (Span 4) --}}
        <div class="card-modern" style="grid-column: span 4;">
            <h5>❤️ Top 3 Produk Favorite (Like)</h5>
            <div class="chart-box"><canvas id="chartFavorite"></canvas></div>
        </div>

        {{-- CHART 5: PRODUK DILIHAT (Span 4) --}}
        <div class="card-modern" style="grid-column: span 4;">
            <h5>👁️ Top 3 Produk Dilihat</h5>
            <div class="chart-box"><canvas id="chartViews"></canvas></div>
        </div>

        {{-- CHART 6: TOP USER (Pembeli) (Span 6) --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>👥 Top 3 User Aktif (Jumlah Transaksi)</h5>
            <div class="chart-box"><canvas id="chartUser"></canvas></div>
        </div>

    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data Chart harus dikonversi dari Blade PHP
        let data = {
            pendapatan: @json($pendapatanChart ?? ['labels' => [], 'data' => []]),
            kategori: @json($kategoriChart ?? ['labels' => [], 'data' => []]),
            terlaris: @json($produkTerlarisChart ?? ['labels' => [], 'data' => []]),
            favorite: @json($produkFavoriteChart ?? ['labels' => [], 'data' => []]),
            views: @json($produkViewChart ?? ['labels' => [], 'data' => []]),
            user: @json($transaksiUserChart ?? ['labels' => [], 'data' => []]),
        };

        const primaryColors = ['#5ab1f7', '#7bd2f6', '#32a852', '#f6931d', '#9b59b6', '#3498db'];

        function chart(id, type, labels, dataset, horizontal = false) {
            if (!labels || labels.length === 0) {
                const el = document.getElementById(id);
                if (el) el.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 50px;">Tidak ada data untuk filter ini.</p>';
                return;
            }

            const el = document.getElementById(id);
            if (!el) return;

            let chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: type === 'doughnut',
                        labels: {
                            color: '#b8ccdf'
                        }
                    },
                    tooltip: {}
                },
                scales: (type === 'doughnut') ? {} : {
                    x: {
                        ticks: {
                            color: '#b8ccdf'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.08)'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#b8ccdf'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.08)'
                        },
                        beginAtZero: true
                    }
                }
            };

            if (horizontal && type === 'bar') {
                chartOptions.indexAxis = 'y';
            }

            // Custom Rupiah logic for Y axis and Tooltip
            if (id === 'chartPendapatan') {
                chartOptions.scales.y.ticks.callback = function(value) {
                    if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1) + ' Jt';
                    if (value >= 1000) return 'Rp' + (value / 1000).toFixed(0) + ' Rb';
                    return 'Rp' + value;
                };
                chartOptions.plugins.tooltip = {
                    callbacks: {
                        label: (context) => 'Rp ' + context.parsed.y.toLocaleString('id-ID')
                    }
                };
            }

            // Custom Tooltip for Qty (default for all other charts)
            if (id !== 'chartPendapatan') {
                chartOptions.plugins.tooltip = {
                    callbacks: {
                        label: (context) => {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            return label + context.parsed.y.toLocaleString('id-ID') + (id !== 'chartUser' ?
                                ' unit' : '');
                        }
                    }
                };
            }


            let datasets = [{
                data: dataset,
                backgroundColor: primaryColors,
                borderColor: '#102544',
                borderWidth: (type === 'doughnut') ? 3 : 1,
            }];

            new Chart(el, {
                type: type,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: chartOptions
            });
        }


        // --- Inisialisasi grafik ---

        // Pendapatan Per Usaha
        chart('chartPendapatan', 'bar', data.pendapatan.labels, data.pendapatan.data, false);

        // Top Kategori (Donut Chart)
        chart('chartKategori', 'doughnut', data.kategori.labels, data.kategori.data, false);

        // Chart lainnya (Horizontal bar)
        chart('chartTerlaris', 'bar', data.terlaris.labels, data.terlaris.data, true);
        chart('chartFavorite', 'bar', data.favorite.labels, data.favorite.data, true);
        chart('chartViews', 'bar', data.views.labels, data.views.data, true);
        chart('chartUser', 'bar', data.user.labels, data.user.data, true);
    </script>
@stop
