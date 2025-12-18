@extends('adminlte::page')

@section('title', 'Dashboard Laporan')

@section('content')

    {{-- FILTER GLOBAL --}}
    @include('admin.laporan_usaha.filter', [
        'action' => route('admin.laporan_usaha.index'),
        'resetUrl' => route('admin.laporan_usaha.index'),
        'showUsaha' => true,
        'showKategori' => true,
        'showPengerajin' => true,
        'showDateRange' => false, // dashboard pakai periode_type saja
        'showPeriode' => true,
        'showStatus' => false,
    ])

    {{-- GRID DASHBOARD --}}
    <div class="dashboard-grid">

        {{-- METRIC ATAS --}}
        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">Total Transaksi</div>
            <div class="metric-value">{{ number_format($totalTransaksi ?? 0, 0, ',', '.') }}</div>
        </div>

        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">Total Pendapatan</div>
            <div class="metric-value">
                Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">Produk Terlaris (Penjualan)</div>
            <div class="metric-value" style="font-size: 22px;">
                {{ $topProduk ?? '-' }}
            </div>
        </div>

        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">User Aktif Tertinggi</div>
            <div class="metric-value" style="font-size: 22px;">
                {{ $userAktif ?? '-' }}
            </div>
        </div>

        {{-- PENDAPATAN TOP 3 USAHA --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>💰 Pendapatan Top 3 Usaha</h5>
            <div class="chart-box"><canvas id="chartPendapatan"></canvas></div>
        </div>

        {{-- PENDAPATAN TOP 3 PENGERAJIN --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>👷‍♂️ Pendapatan Top 3 Pengerajin</h5>
            <div class="chart-box"><canvas id="chartPendapatanPengerajin"></canvas></div>
        </div>

        {{-- PERFORMA PENJUALAN 5 USAHA TERATAS (LINE) --}}
        <div class="card-modern" style="grid-column: span 12;">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h5 class="mb-0">📈 Performa Penjualan 5 Usaha Teratas</h5>
                @if ($periodeLabel)
                    <span style="font-size: 12px; opacity:.8;">Periode: {{ $periodeLabel }}</span>
                @endif
            </div>
            <div class="chart-box"><canvas id="chartPerforma"></canvas></div>
        </div>

        {{-- TOP 3 KATEGORI --}}
        <div class="card-modern" style="grid-column: span 4;">
            <h5>📦 Top 3 Kategori Produk (Total Terjual)</h5>
            <div class="chart-box"><canvas id="chartKategori"></canvas></div>
        </div>

        {{-- TOP 3 PRODUK: TERLARIS / FAVORITE / DILIHAT --}}
        <div class="card-modern" style="grid-column: span 4;">
            <h5>🔥 Top 3 Produk Terlaris (Penjualan)</h5>
            <div class="chart-box"><canvas id="chartTerlaris"></canvas></div>
        </div>

        <div class="card-modern" style="grid-column: span 4;">
            <h5>❤️ Top 3 Produk Favorite (Like)</h5>
            <div class="chart-box"><canvas id="chartFavorite"></canvas></div>
        </div>

        <div class="card-modern" style="grid-column: span 4;">
            <h5>👁️ Top 3 Produk Dilihat</h5>
            <div class="chart-box"><canvas id="chartViews"></canvas></div>
        </div>

        {{-- TOP 3 USER (Pembeli) --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>👥 Top 3 User Aktif (Jumlah Transaksi)</h5>
            <div class="chart-box"><canvas id="chartUser"></canvas></div>
        </div>

    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let data = {
            pendapatan: @json($pendapatanChart ?? ['labels' => [], 'data' => []]),
            pendapatanPengerajin: @json($pendapatanPengerajinChart ?? ['labels' => [], 'data' => []]),
            performa: @json($performaPenjualanChart ?? ['labels' => [], 'datasets' => []]),
            terlaris: @json($produkTerlarisChart ?? ['labels' => [], 'data' => []]),
            favorite: @json($produkFavoriteChart ?? ['labels' => [], 'data' => []]),
            views: @json($produkViewChart ?? ['labels' => [], 'data' => []]),
            user: @json($transaksiUserChart ?? ['labels' => [], 'data' => []]),
            kategori: @json($kategoriChart ?? ['labels' => [], 'data' => []]),
        };

        const primaryColors = ['#5ab1f7', '#7bd2f6', '#32a852', '#f6931d', '#9b59b6', '#3498db'];

        // Chart helper utk bar / doughnut
        function chartBasic(id, type, labels, dataset, horizontal = false) {
            if (!labels || labels.length === 0) {
                const el = document.getElementById(id);
                if (el) el.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 50px;">Tidak ada data untuk filter ini.</p>';
                return;
            }

            const el = document.getElementById(id);
            if (!el) return;

            let options = {
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
                        }
                    }
                }
            };

            if (horizontal && type === 'bar') {
                options.indexAxis = 'y';
            }

            // Khusus chart pendapatan, pakai format Rupiah
            if (id === 'chartPendapatan' || id === 'chartPendapatanPengerajin') {
                options.scales.y.ticks.callback = function(value) {
                    if (value >= 1000000000) return 'Rp' + (value / 1000000000).toFixed(1) + ' M';
                    if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1) + ' Jt';
                    if (value >= 1000) return 'Rp' + (value / 1000).toFixed(0) + ' Rb';
                    return 'Rp' + value;
                };
                options.plugins.tooltip.callbacks = {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.parsed.y !== null) {
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                        return label;
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
                options: options
            });
        }

        // Chart helper utk line multi-usaha (performa penjualan)
        function chartPerformaLine(id, labels, datasetsConfig) {
            if (!labels || labels.length === 0 || !datasetsConfig || datasetsConfig.length === 0) {
                const el = document.getElementById(id);
                if (el) el.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 50px;">Tidak ada data untuk filter ini.</p>';
                return;
            }

            const el = document.getElementById(id);
            if (!el) return;

            const datasets = datasetsConfig.map((ds, idx) => ({
                label: ds.label,
                data: ds.data,
                borderColor: primaryColors[idx % primaryColors.length],
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 4,
                fill: false,
            }));

            new Chart(el, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
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
                                    let label = context.dataset.label ? context.dataset.label + ': ' : '';
                                    const value = context.parsed.y ?? 0;
                                    return label + 'Rp ' + value.toLocaleString('id-ID');
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
                            beginAtZero: true,
                            ticks: {
                                color: '#b8ccdf',
                                callback: function(value) {
                                    if (value >= 1000000000) return 'Rp' + (value / 1000000000).toFixed(1) +
                                        ' M';
                                    if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1) + ' Jt';
                                    if (value >= 1000) return 'Rp' + (value / 1000).toFixed(0) + ' Rb';
                                    return 'Rp' + value;
                                }
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        }
                    }
                }
            });
        }

        // Inisialisasi chart2

        // Pendapatan usaha & pengerajin
        chartBasic('chartPendapatan', 'bar', data.pendapatan.labels, data.pendapatan.data, false);
        chartBasic('chartPendapatanPengerajin', 'bar', data.pendapatanPengerajin.labels, data.pendapatanPengerajin.data,
            false);

        // Performa penjualan (line)
        chartPerformaLine('chartPerforma', data.performa.labels, data.performa.datasets);

        // Kategori (donut)
        chartBasic('chartKategori', 'doughnut', data.kategori.labels, data.kategori.data, false);

        // Chart lainnya (horizontal bar)
        chartBasic('chartTerlaris', 'bar', data.terlaris.labels, data.terlaris.data, true);
        chartBasic('chartFavorite', 'bar', data.favorite.labels, data.favorite.data, true);
        chartBasic('chartViews', 'bar', data.views.labels, data.views.data, true);
        chartBasic('chartUser', 'bar', data.user.labels, data.user.data, true);
    </script>
@stop
