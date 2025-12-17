@extends('pengerajin.layouts.pengerajin')

@section('title', 'Produk Slow Moving')

@section('content_header')
    <h1 style="color:white; font-weight:600;">Produk Slow Moving</h1>
@stop

@section('content')
    @include('pengerajin.laporan_usaha.filter', [
        'action' => route('pengerajin.laporan_usaha.produk-slow-moving'),
        'resetUrl' => route('pengerajin.laporan_usaha.produk-slow-moving'),
        'showUsaha' => true,
        'showKategori' => true,
        'showStatus' => true,
        'showDateRange' => true,
        'showPeriode' => true,
        'exportRoute' => 'pengerajin.laporan_usaha.produk-slow-moving.export',
    ])
    {{-- 📊 RINGKASAN --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Produk Slow Moving</div>
                    <div class="metric-value">
                        {{ number_format($totalProdukSlow ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft-warning mt-2">
                        Produk dengan total terjual &lt; {{ $threshold ?? 5 }} pada periode ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Unit Terjual (Slow)</div>
                    <div class="metric-value">
                        {{ number_format($totalQtyTerjual ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft-warning mt-2">
                        Akumulasi jumlah terjual semua produk slow moving
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Periode Analisis</div>
                    <div class="metric-value">
                        {{ \Carbon\Carbon::parse($start)->format('d-m-Y') }}
                        s/d
                        {{ \Carbon\Carbon::parse($end)->format('d-m-Y') }}
                    </div>
                    <span class="badge badge-soft-warning mt-2">
                        Ubah tanggal di filter untuk melihat periode berbeda
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 📋 TABEL + GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 15px;">Daftar Produk Slow Moving</h3>
        </div>

        <div class="card-body">
            {{-- TABEL --}}
            <div class="table-responsive mb-3">
                <table class="table table-dark-custom table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Usaha</th>
                            <th>Nama Produk</th>
                            <th class="text-right" style="width:140px;">Total Terjual</th>
                            <th class="text-center" style="width:180px;">Transaksi Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row->nama_usaha ?? '-' }}</td>
                                <td>{{ $row->nama_produk }}</td>
                                <td class="text-right">
                                    {{ number_format($row->total_terjual, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if ($row->transaksi_terakhir)
                                        {{ \Carbon\Carbon::parse($row->transaksi_terakhir)->format('d-m-Y H:i') }}
                                    @else
                                        <span style="opacity:.7;">Belum pernah terjual di periode ini</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center" style="opacity:.7; padding: 16px;">
                                    Tidak ada produk slow moving untuk filter/periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- GRAFIK --}}
            <div>
                <canvas id="slowMovingChart"></canvas>
            </div>
        </div>

        @if (($laporan ?? collect())->count() > 0)
            <div class="card-footer" style="font-size: 12px; opacity:.75;">
                <i class="fa fa-info-circle"></i>
                Produk dengan penjualan rendah dapat dipertimbangkan untuk <strong>promo khusus</strong>,
                pengurangan stok, atau penggantian varian.
            </div>
        @endif
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            const canvas = document.getElementById('slowMovingChart');
            if (!canvas) return;

            const raw = @json($laporan);

            if (!raw.length) {
                canvas.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 40px;">Tidak ada data untuk ditampilkan.</p>';
                return;
            }

            // Ambil maksimal 20 produk slow moving teratas untuk grafik
            const top = raw.slice(0, 20);

            const labels = top.map(r => r.nama_produk);
            const values = top.map(r => Number(r.total_terjual));

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Terjual (Slow Moving)',
                        data: values,
                        backgroundColor: 'rgba(255, 206, 86, 0.7)',
                        borderColor: 'rgba(255, 206, 86, 1)',
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
                                    const val = context.parsed.y ?? 0;
                                    return ' ' + val.toLocaleString('id-ID') + ' unit';
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
