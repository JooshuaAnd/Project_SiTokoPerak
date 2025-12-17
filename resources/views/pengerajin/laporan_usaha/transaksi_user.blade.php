@extends('pengerajin.layouts.pengerajin')

@section('title', 'Transaksi Per User')

@section('content_header')
    <h1 style="color:black; font-weight:600;">Laporan Transaksi Per User</h1>
@stop

@section('content')
    @include('pengerajin.laporan_usaha.filter', [
        'action' => route('pengerajin.laporan_usaha.transaksi-user'),
        'resetUrl' => route('pengerajin.laporan_usaha.transaksi-user'),
        'showUsaha' => true,
        'showKategori' => true,
        'showStatus' => true,
        'showDateRange' => true,
        'showPeriode' => true,
        'exportRoute' => 'pengerajin.laporan_usaha.transaksi-user.export',
    ])
    <div class="row mt-2">
        <div class="form-group col-md-3 col-sm-6" style="margin-top: 8px;">
            <button type="submit" class="btn btn-primary btn-block mb-2">
                <i class="fa fa-filter"></i> Terapkan
            </button>
            <a href="{{ route('pengerajin.laporan_usaha.transaksi-user') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-sync-alt"></i> Reset
            </a>

            <a href="{{ route('pengerajin.laporan_usaha.transaksi-user.export', request()->query()) }}"
                class="btn btn-success btn-block mt-2">
                <i class="fa fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
    </form>
    </div>
    </div>


    {{-- RINGKASAN --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total User Aktif</div>
                    <div class="metric-value">
                        {{ number_format($totalUser ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        User yang memiliki transaksi pada filter ini
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Transaksi</div>
                    <div class="metric-value">
                        {{ number_format($totalTransaksi ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Jumlah transaksi yang tercakup dalam laporan
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-body">
                    <div class="metric-label">Total Belanja</div>
                    <div class="metric-value">
                        Rp {{ number_format($totalBelanja ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="badge badge-soft mt-2">
                        Akumulasi nilai belanja seluruh user
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD TABEL + GRAFIK --}}
    <div class="card card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title" style="font-size: 15px;">Performa Transaksi Per User</h3>

            <div class="toggle-pill" id="toggleViewUser">
                <button type="button" class="active" data-view="table">Tabel</button>
                <button type="button" data-view="chart">Grafik</button>
            </div>
        </div>

        <div class="card-body" style="min-height: 320px;">

            {{-- VIEW TABEL (DataTables) --}}
            <div id="view-user-table">
                <div class="table-responsive">
                    <table id="tableTransaksiUser" class="table table-dark-custom table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Total Transaksi</th>
                                <th>Total Belanja (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->username }}</td>
                                    <td>{{ number_format($row->total_transaksi, 0, ',', '.') }}</td>
                                    <td>{{ number_format($row->total_belanja, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center" style="opacity:.7; padding: 16px;">
                                        Tidak ada data transaksi untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- VIEW GRAFIK --}}
            <div id="view-user-chart" class="d-none">
                <canvas id="transaksiUserChart"></canvas>
            </div>
        </div>

        @if (($totalBelanja ?? 0) > 0)
            <div class="card-footer" style="font-size: 12px; opacity: .75;">
                <i class="fa fa-info-circle"></i>
                User dengan <strong>total belanja tertinggi</strong> bisa diprioritaskan untuk loyalty program,
                promosi khusus, atau pendekatan personal.
            </div>
        @endif
    </div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

    <script>
        // Toggle Tabel / Grafik
        (function() {
            const toggle = document.getElementById('toggleViewUser');
            if (!toggle) return;

            const btns = toggle.querySelectorAll('button');
            const viewTable = document.getElementById('view-user-table');
            const viewChart = document.getElementById('view-user-chart');

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

        // Inisialisasi DataTables
        $(document).ready(function() {
            $('#tableTransaksiUser').DataTable({
                pageLength: 10,
                order: [
                    [3, 'desc']
                ], // sort default by total belanja
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Prev"
                    },
                    zeroRecords: "Tidak ada data yang cocok",
                }
            });
        });

        // Chart Transaksi Per User
        (function() {
            const canvas = document.getElementById('transaksiUserChart');
            if (!canvas) return;

            const data = @json($laporan);
            if (!data.length) {
                canvas.parentNode.innerHTML =
                    '<p style="text-align:center; opacity:0.6; padding-top: 40px;">Tidak ada data untuk ditampilkan.</p>';
                return;
            }

            const labels = data.map(r => r.username);
            const values = data.map(r => Number(r.total_belanja));

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Belanja (Rp)',
                        data: values,
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
                                    return ' Rp ' + value.toLocaleString('id-ID');
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
                                    return 'Rp ' + value.toLocaleString('id-ID');
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
