@extends('pengerajin.layouts.pengerajin')

@section('title', 'Laporan Views Produk')
@section('content')
    @include('pengerajin.laporan_usaha.filter', [
        'action' => route('pengerajin.laporan_usaha.produk-views'),
        'resetUrl' => route('pengerajin.laporan_usaha.produk-views'),
        'showUsaha' => true,
        'showKategori' => true,
        'showStatus' => true,
        'showDateRange' => true,
        'showPeriode' => true,
        'exportRoute' => 'pengerajin.laporan_usaha.produk-views.export',
    ])


    {{-- 📊 RINGKASAN --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Produk</div>
                    <div class="metric-value">
                        {{ number_format($totalProduk ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Total produk terdaftar
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Produk Tercatat Views</div>
                    <div class="metric-value">
                        {{ number_format($produkDenganViews ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Produk yang pernah diklik (punya views > 0)
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Views</div>
                    <div class="metric-value">
                        {{ number_format($totalViews ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Total klik / kunjungan detail produk
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 📋 CARD TABEL / GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size: 15px;">Performa Views per Produk</h3>

            {{-- Toggle --}}
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
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Hanya tampilkan produk yang punya views > 0 biar lebih informatif
                                $rows = $produkViews->where('total_views', '>', 0);
                            @endphp

                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row->nama_produk }}</td>
                                    <td class="text-right">{{ number_format($row->total_views, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center" style="opacity:.7; padding: 16px;">
                                        Belum ada data views produk untuk filter ini.
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

        @if (($produkViews->sum('total_views') ?? 0) > 0)
            <div class="card-footer" style="font-size: 12px; opacity: .75;">
                <i class="fa fa-info-circle"></i>
                Produk dengan <strong>views</strong> tertinggi menunjukkan minat pengunjung yang besar.
                Gunakan data ini sebagai bahan pertimbangan untuk promosi & penataan katalog.
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

            const allData = @json($produkViews);
            // Ambil hanya yang punya views > 0
            const filtered = allData.filter(row => Number(row.total_views) > 0);

            if (!filtered.length) {
                canvas.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 40px;">Tidak ada data untuk ditampilkan.</p>';
                return;
            }

            const labels = filtered.map(r => r.nama_produk);
            const views = filtered.map(r => Number(r.total_views));

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Views',
                        data: views,
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
