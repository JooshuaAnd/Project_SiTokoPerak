@extends('adminlte::page')

@section('title', 'Dashboard Laporan')

@section('content')

    {{-- FILTER GLOBAL + EXPORT --}}
    @include('admin.laporan_usaha.filter', [
        'action' => route('admin.laporan_usaha.index'),
        'resetUrl' => route('admin.laporan_usaha.index'),
        'showUsaha' => true,
        'showKategori' => true,
        'showPengerajin' => true,
        'showDateRange' => false, // Tidak ditampilkan di sini, tapi bisa diaktifkan
        'showPeriode' => true, // Tidak ditampilkan di sini, tapi bisa diaktifkan
        'showStatus' => false, // Tidak ditampilkan di sini, tapi bisa diaktifkan
        // 'exportRoute' => 'admin.laporan_usaha.export', // Jika ada route export
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

        {{-- BAR PENDAPATAN --}}
        {{-- BAGIAN 1: PENDAPATAN PER USAHA (Span 6) --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>💰 Pendapatan Top 3 Usaha</h5>
            <div class="chart-box"><canvas id="chartPendapatan"></canvas></div>
        </div>

        {{-- BAGIAN 2: PENDAPATAN PER PENGERAJIN (Span 6) --}}
        {{-- KITA GANTI TOP KATEGORI AGAR BERdampingan DENGAN CHART BAR PENGERAJIN --}}
        <div class="card-modern" style="grid-column: span 6;">
            <h5>👷‍♂️ Pendapatan Top 3 Pengerajin</h5>
            {{-- ID CANVAS BARU --}}
            <div class="chart-box"><canvas id="chartPendapatanPengerajin"></canvas></div>
        </div>

        {{-- KITA GESER TOP KATEGORI AGAR MENGGUNAKAN GRID YANG LEBIH BAIK --}}
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
            // PERBAIKAN 1: Tambahkan data Pengerajin
            pendapatan: @json($pendapatanChart ?? ['labels' => [], 'data' => []]),
            pendapatanPengerajin: @json($pendapatanPengerajinChart ?? ['labels' => [], 'data' => []]),
            terlaris: @json($produkTerlarisChart ?? ['labels' => [], 'data' => []]),
            favorite: @json($produkFavoriteChart ?? ['labels' => [], 'data' => []]),
            views: @json($produkViewChart ?? ['labels' => [], 'data' => []]),
            user: @json($transaksiUserChart ?? ['labels' => [], 'data' => []]),
            kategori: @json($kategoriChart ?? ['labels' => [], 'data' => []]),
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
                    }
                },
                scales: (type === 'doughnut') ?
                    {} :
                    {
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
                chartOptions.indexAxis = 'y';
            }

            // PERBAIKAN 2: Gunakan ID chartPendapatan DAN chartPendapatanPengerajin untuk Custom Rupiah
            if (id === 'chartPendapatan' || id === 'chartPendapatanPengerajin') {
                chartOptions.scales.y.ticks.callback = function(value) {
                    if (value >= 1000000000) return 'Rp' + (value / 1000000000).toFixed(1) + ' M';
                    if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1) + ' Jt';
                    if (value >= 1000) return 'Rp' + (value / 1000).toFixed(0) + ' Rb';
                    return 'Rp' + value;
                };
                chartOptions.plugins.tooltip = {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                            return label;
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

        // Inisialisasi grafik

        // PERBAIKAN 3: Inisialisasi Chart Pendapatan Pengerajin
        chart('chartPendapatan', 'bar', data.pendapatan.labels, data.pendapatan.data, false);
        chart('chartPendapatanPengerajin', 'bar', data.pendapatanPengerajin.labels, data.pendapatanPengerajin.data, false);

        // Kategori diubah dari span 4 (Donut) agar berdampingan dengan chart bar
        chart('chartKategori', 'doughnut', data.kategori.labels, data.kategori.data, false);

        // Chart lainnya (horizontal bar)
        chart('chartTerlaris', 'bar', data.terlaris.labels, data.terlaris.data, true);
        chart('chartFavorite', 'bar', data.favorite.labels, data.favorite.data, true);
        chart('chartViews', 'bar', data.views.labels, data.views.data, true);
        chart('chartUser', 'bar', data.user.labels, data.user.data, true);
    </script>
@stop
