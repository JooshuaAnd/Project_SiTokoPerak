@extends('pengerajin.layouts.pengerajin')
@section('title', 'Dashboard Laporan')

@section('content')
    {{-- 🔍 FILTER GLOBAL (Card yang membungkus semua filter) --}}
    @include('pengerajin.laporan_usaha.filter', [
        'action' => route('pengerajin.laporan_usaha.index'),
        'resetUrl' => route('pengerajin.laporan_usaha.index'),
        'showUsaha' => true, // Pastikan ini true jika ingin menampilkan filter usaha
        'showStatus' => true,
        'showKategori' => true,
        'showDateRange' => true,
        'showPeriode' => true,
        'showPengerajin' => true,
        // nggak perlu export di dashboard => nggak kita isi 'exportRoute'
    ])

    {{-- GRID DASHBOARD UTAMA --}}
    <div class="dashboard-grid">

        {{-- METRIC ATAS (4 KOLOM, Span 3) --}}
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
                {{ $topProduk ?? 'N/A' }}
            </div>
        </div>

        <div class="card-modern" style="grid-column: span 3;">
            <div class="metric-label">User Aktif Tertinggi</div>
            <div class="metric-value" style="font-size: 22px;">
                {{ $userAktif ?? 'N/A' }}
            </div>
        </div>

        {{-- BAR PENDAPATAN PER USAHA (Span 6) --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>💰 Pendapatan Top 3 Usaha (Yang Anda kelola)</h5>
            <div class="chart-box"><canvas id="chartPendapatan"></canvas></div>
        </div>

        {{-- TOP KATEGORI (Span 6) --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>📦 Top 3 Kategori Produk (Total Terjual)</h5>
            <div class="chart-box"><canvas id="chartKategori"></canvas></div>
        </div>

        {{-- TOP 3 PRODUK: TERLARIS (Span 4) --}}
        <div class="card-modern" style="grid-column: span 4;">
            <h5>🔥 Top 3 Produk Terlaris (Penjualan)</h5>
            <div class="chart-box"><canvas id="chartTerlaris"></canvas></div>
        </div>

        {{-- TOP 3 PRODUK: FAVORITE (Span 4) --}}
        <div class="card-modern" style="grid-column: span 4;">
            <h5>❤️ Top 3 Produk Favorite (Like)</h5>
            <div class="chart-box"><canvas id="chartFavorite"></canvas></div>
        </div>

        {{-- TOP 3 PRODUK: DILIHAT (Span 4) --}}
        <div class="card-modern" style="grid-column: span 4;">
            <h5>👁️ Top 3 Produk Dilihat</h5>
            <div class="chart-box"><canvas id="chartViews"></canvas></div>
        </div>

        {{-- TOP 3 USER (Pembeli) (Span 6) --}}
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

            const isHorizontalBar = horizontal && type === 'bar';

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

            if (isHorizontalBar) {
                chartOptions.indexAxis = 'y';
            }

            // === TOOLTIP & VALUE HANDLER YANG BENAR ===
            chartOptions.plugins.tooltip = {
                callbacks: {
                    label: (context) => {
                        let value;

                        if (type === 'doughnut') {
                            // Doughnut → nilai langsung di parsed (number)
                            value = context.parsed;
                        } else if (isHorizontalBar) {
                            // Bar horizontal → value di sumbu X
                            value = context.parsed.x;
                        } else {
                            // Bar vertikal → value di sumbu Y
                            value = context.parsed.y;
                        }

                        if (id === 'chartPendapatan') {
                            // Tooltip khusus pendapatan (Rupiah)
                            return 'Rp ' + (value ?? 0).toLocaleString('id-ID');
                        }

                        let label = context.label || '';
                        if (label) label += ': ';
                        const suffix = (id !== 'chartUser') ? ' unit' : '';
                        return label + (value ?? 0).toLocaleString('id-ID') + suffix;
                    }
                }
            };

            // Khusus axis pendapatan (bar VERTIKAL)
            if (id === 'chartPendapatan' && type === 'bar' && !isHorizontalBar) {
                chartOptions.scales.y.ticks.callback = function(value) {
                    if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1) + ' Jt';
                    if (value >= 1000) return 'Rp' + (value / 1000).toFixed(0) + ' Rb';
                    return 'Rp' + value;
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

        // Pendapatan Per Usaha (bar vertikal)
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
