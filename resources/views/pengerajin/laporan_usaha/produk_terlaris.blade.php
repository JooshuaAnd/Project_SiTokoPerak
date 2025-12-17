@extends('pengerajin.layouts.pengerajin')

@section('title', 'Laporan Produk Terlaris')

@section('content_header')
    <h1 style="color:white; font-weight:600;">Laporan Produk Terlaris</h1>
@stop

@section('content')
    @include('pengerajin.laporan_usaha.filter', [
        'action' => route('pengerajin.laporan_usaha.produk-terlaris'),
        'resetUrl' => route('pengerajin.laporan_usaha.produk-terlaris'),
        'showUsaha' => true,
        'showKategori' => true,
        'showStatus' => true,
        'showDateRange' => true,
        'showPeriode' => true,
        'exportRoute' => 'pengerajin.laporan_usaha.produk-terlaris.export',
    ])
    {{-- 📊 RINGKASAN --}}
    @php
        $topNama = $topRow->nama_produk ?? '-';
        $topQty = $topRow->total_terjual ?? 0;
    @endphp

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Produk Terjual (Distinct)</div>
                    <div class="metric-value">
                        {{ number_format($totalProduk, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Jumlah produk yang muncul dalam laporan ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Kuantitas Terjual</div>
                    <div class="metric-value">
                        {{ number_format($totalTerjual, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Akumulasi jumlah item yang terjual dalam periode/filter
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Produk Paling Laris</div>
                    <div class="metric-value">
                        {{ $topNama }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Terjual {{ number_format($topQty, 0, ',', '.') }} item
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 📋 CARD TABEL / GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size: 15px;">Performa Produk Terlaris</h3>

            <div class="toggle-pill" id="toggleViewTerlaris">
                <button type="button" class="active" data-view="table">Tabel</button>
                <button type="button" data-view="chart">Grafik</button>
            </div>
        </div>

        <div class="card-body" style="min-height: 320px;">

            {{-- VIEW TABEL --}}
            <div id="view-terlaris-table">
                <div class="table-responsive">
                    <table class="table table-dark-custom table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Nama Produk</th>
                                <th class="text-right" style="width:150px;">Total Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->nama_produk }}</td>
                                    <td class="text-right">
                                        {{ number_format($row->total_terjual, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center" style="opacity:.7; padding: 16px;">
                                        Tidak ada data produk terlaris untuk filter/periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- VIEW GRAFIK --}}
            <div id="view-terlaris-chart" class="d-none">
                <canvas id="produkTerlarisChart"></canvas>
            </div>

        </div>

        @if ($totalTerjual > 0)
            <div class="card-footer" style="font-size: 12px; opacity: .75;">
                <i class="fa fa-info-circle"></i>
                Fokuskan stok & promosi pada produk dengan <strong>penjualan tertinggi</strong>.
                Pertimbangkan juga paket bundling dengan produk lain yang penjualannya rendah.
            </div>
        @endif
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Toggle Tabel / Grafik
        (function() {
            const toggle = document.getElementById('toggleViewTerlaris');
            if (!toggle) return;

            const btns = toggle.querySelectorAll('button');
            const viewTable = document.getElementById('view-terlaris-table');
            const viewChart = document.getElementById('view-terlaris-chart');

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

        // CHART Produk Terlaris (pakai data top 10 dari controller)
        (function() {
            const canvas = document.getElementById('produkTerlarisChart');
            if (!canvas) return;

            const chartData = @json($chartData);

            if (!chartData.length) {
                canvas.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 40px;">Tidak ada data untuk ditampilkan.</p>';
                return;
            }

            const labels = chartData.map(r => r.nama_produk);
            const qty = chartData.map(r => Number(r.total_terjual));

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Terjual',
                        data: qty,
                        backgroundColor: 'rgba(255, 159, 64, 0.8)',
                        borderColor: 'rgba(255, 159, 64, 1)',
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
