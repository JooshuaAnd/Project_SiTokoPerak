@extends('pengerajin.layouts.pengerajin')
@section('title', 'Dashboard Laporan')

@section('content')

    {{-- HEADER DAN NAVIGASI (Hanya Judul) --}}
    <h1 style="color:white; font-weight:600;">Dashboard Laporan</h1>
    <hr style="color:#b8ccdf;">


    {{-- 🔍 FILTER GLOBAL (Card yang membungkus semua filter) --}}
    @include('pengerajin.laporan_usaha.filter', [
        'action' => route('pengerajin.laporan_usaha.index'),
        'resetUrl' => route('pengerajin.laporan_usaha.index'),
        'showTahun' => true,
        'showBulan' => true,
        'showUsaha' => true,
        'showKategori' => true,
        'showDateRange' => true, // di dashboard pakai tahun/bulan saja
        'showPeriode' => true, // kalau nggak mau opsi Per Hari/Bulan/Tahun di sini
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
        // Data Chart harus dikonversi dari Blade PHP
        let data = {
            pendapatan: @json($pendapatanChart ?? ['labels' => [], 'data' => []]),
            // Ganti pendapatanPengerajinChart dengan kategoriChart karena layout diubah
            kategori: @json($kategoriChart ?? ['labels' => [], 'data' => []]),
            terlaris: @json($produkTerlarisChart ?? ['labels' => [], 'data' => []]),
            favorite: @json($produkFavoriteChart ?? ['labels' => [], 'data' => []]),
            views: @json($produkViewChart ?? ['labels' => [], 'data' => []]),
            user: @json($transaksiUserChart ?? ['labels' => [], 'data' => []]),
        };

        // Cek apakah ada data Pendapatan Pengerajin yang tidak relevan di Admin yang terlanjur tercompact,
        // kita abaikan karena tidak ada di Controller Pengerajin.

        const primaryColors = ['#5ab1f7', '#7bd2f6', '#32a852', '#f6931d', '#9b59b6', '#3498db'];

        function chart(id, type, labels, dataset, horizontal = false) {
            // ... (Fungsi chart sama seperti yang Anda berikan di jawaban sebelumnya) ...
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
                        label: (context) => context.dataset.label + ': ' + context.parsed.y.toLocaleString('id-ID') +
                            ' unit'
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

        // Menghilangkan chart Pendapatan Pengerajin yang tidak perlu (chartPendapatanPengerajin)
        // karena ID chartPendapatanPengerajin sudah dihapus dari HTML di atas.
    </script>
@stop
