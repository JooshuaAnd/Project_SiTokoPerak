@extends('adminlte::page')


@section('title', 'Produk Slow Moving')

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

        .report-nav {
            background: #0f233f;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 18px;
        }

        .report-nav a {
            display: block;
            padding: 10px 14px;
            color: #b8ccdf;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 6px;
            text-decoration: none;
            transition: 0.2s;
        }

        .report-nav a:hover,
        .report-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-weight: 600;
        }

        .form-control,
        .btn {
            border-radius: 8px !important;
        }

        .form-control {
            background-color: #0b1d39;
            color: #e8eef7;
            border-color: rgba(255, 255, 255, 0.1);
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

        .badge-soft-warning {
            background: rgba(255, 206, 86, 0.1);
            border: 1px solid rgba(255, 206, 86, 0.5);
            color: #ffce56;
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

        #slowMovingChart {
            max-height: 360px;
        }
    </style>
@stop

@section('content_header')
    <h1 style="color:white; font-weight:600;">Produk Slow Moving</h1>
@stop

@section('content')

    {{-- 🔍 FILTER --}}
    <div class="card-modern mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.laporan_usaha.produk-slow-moving') }}">
                <div class="row">
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Usaha</label>
                        <select name="usaha_id" class="form-control">
                            <option value="">Semua Usaha</option>
                            @foreach ($usahaList as $usaha)
                                <option value="{{ $usaha->id }}"
                                    {{ (string) request('usaha_id') === (string) $usaha->id ? 'selected' : '' }}>
                                    {{ $usaha->nama_usaha }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Kategori Produk</label>
                        <select name="kategori_id" class="form-control">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoriList as $kategori)
                                <option value="{{ $kategori->id }}"
                                    {{ (string) request('kategori_id') === (string) $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control"
                            value="{{ request('start_date', $start ?? '') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control"
                            value="{{ request('end_date', $end ?? '') }}">
                    </div>
                </div>

                {{-- ✅ Tambah filter periode (untuk override default 30 hari) --}}
                @include('admin.laporan_usaha.partials.filter_periode')

                <div class="row mt-2">
                    <div class="form-group col-md-3 col-sm-6" style="margin-top: 4px;">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fa fa-filter"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.laporan_usaha.produk-slow-moving') }}" class="btn btn-secondary btn-block">
                            <i class="fa fa-sync-alt"></i> Reset
                        </a>

                        <a href="{{ route('admin.laporan_usaha.produk-slow-moving.export', request()->query()) }}"
                            class="btn btn-success btn-block mt-2">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </div>

                @if (!request('start_date') && !request('end_date') && !request('periode_type'))
                    <small style="color:#b8ccdf; opacity:.8;">
                        * Jika tidak memilih tanggal & periode, data otomatis memakai
                        <strong>30 hari terakhir</strong>.
                    </small>
                @endif
            </form>
        </div>
    </div>


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
