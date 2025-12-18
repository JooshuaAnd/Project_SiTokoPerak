@extends('adminlte::page')

@section('title', 'Laporan Produk Favorite')

@section('content')
    {{-- 🔍 FILTER --}}
    @include('admin.laporan_usaha.filter', [
        'action' => route('admin.laporan_usaha.produk-favorite'),
        'resetUrl' => route('admin.laporan_usaha.produk-favorite'),
        'showUsaha' => true,
        'showKategori' => true,
        'showStatus' => true,
        'showDateRange' => true,
        'showPeriode' => true,
        'showPengerajin' => true,
        'exportRoute' => 'admin.laporan_usaha.produk-favorite.export',
    ])

    {{-- 📊 RINGKASAN --}}
    @php
        $totalLike = $laporan->sum('total_like');
        $produkDenganLike = $laporan->where('total_like', '>', 0)->count();
        // fallback: kalau controller belum kirim $totalProduk, pakai jumlah baris laporan
        $totalProdukSummary = $totalProduk ?? $laporan->count();
    @endphp

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Produk (Ringkasan)</div>
                    <div class="metric-value">
                        {{ number_format($totalProdukSummary, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Banyaknya produk pada laporan ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Produk Tercatat Like</div>
                    <div class="metric-value">
                        {{ number_format($produkDenganLike, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Produk yang minimal mendapat 1 like
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Like</div>
                    <div class="metric-value">
                        {{ number_format($totalLike, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Total seluruh like pada periode terpilih
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 📋 CARD TABEL / GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size: 15px;">Performa Produk Favorite (Like)</h3>

            {{-- Toggle Tabel / Grafik --}}
            <div class="toggle-pill" id="toggleViewFavorite">
                <button type="button" class="active" data-view="table">Tabel</button>
                <button type="button" data-view="chart">Grafik</button>
            </div>
        </div>

        <div class="card-body" style="min-height: 320px;">

            {{-- VIEW TABEL --}}
            <div id="view-favorite-table">
                <div class="table-responsive">
                    <table class="table table-dark-custom table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Nama Produk</th>
                                <th class="text-right" style="width:150px;">Total Like</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $rows = $laporan->where('total_like', '>', 0);
                            @endphp

                            @forelse($rows as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->nama_produk }}</td>
                                    <td class="text-right">{{ number_format($row->total_like, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center" style="opacity:.7; padding: 16px;">
                                        Tidak ada data produk favorite untuk periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- VIEW GRAFIK --}}
            <div id="view-favorite-chart" class="d-none">
                <canvas id="produkFavoriteChart"></canvas>
            </div>

        </div>

        @if ($totalLike > 0)
            <div class="card-footer" style="font-size: 12px; opacity: .75;">
                <i class="fa fa-info-circle"></i>
                Produk dengan <strong>total like</strong> tertinggi menunjukkan preferensi kuat dari pengunjung.
                Jadikan referensi untuk promo, penempatan etalase, atau rekomendasi utama.
            </div>
        @endif
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle Tabel / Grafik
        (function() {
            const toggle = document.getElementById('toggleViewFavorite');
            if (!toggle) return;

            const btns = toggle.querySelectorAll('button');
            const viewTable = document.getElementById('view-favorite-table');
            const viewChart = document.getElementById('view-favorite-chart');

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

        // CHART FAVORITE
        (function() {
            const canvas = document.getElementById('produkFavoriteChart');
            if (!canvas) return;

            const allData = @json($laporan);
            const filtered = allData.filter(r => Number(r.total_like) > 0);

            if (!filtered.length) {
                canvas.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 40px;">Tidak ada data untuk ditampilkan.</p>';
                return;
            }

            const labels = filtered.map(r => r.nama_produk);
            const likes = filtered.map(r => Number(r.total_like));

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Like',
                        data: likes,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
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
