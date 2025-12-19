{{-- resources/views/pengerajin/laporan_usaha/partials/filter.blade.php --}}

@php
    // ✅ Konfigurasi default (boleh dioverride dari @include)
    $action = $action ?? url()->current();
    $resetUrl = $resetUrl ?? $action;

    $showTahun = $showTahun ?? false;
    $showBulan = $showBulan ?? false;
    $showUsaha = $showUsaha ?? true;
    $showKategori = $showKategori ?? true;
    $showStatus = $showStatus ?? true;
    $showDateRange = $showDateRange ?? false;
    $showPeriode = $showPeriode ?? false; // Default to false for admin filter, can be overridden
    $showPengerajin = $showPengerajin ?? false; // Default to false for admin filter, can be overridden
@endphp
<div class="card card-modern mb-3">
    <div class="card-body">
        <form method="GET" action="{{ $action }}">
            <div class="row">
                {{-- 🔹 Filter Tahun --}}
                @if ($showTahun && isset($tahunList))
                    <div class="form-group col-md-2 col-sm-6">
                        <label style="color:#ecf0f1; font-weight: 600;">Tahun</label>
                        <select name="tahun" class="form-control"
                            style="background-color: #4a627a; color: #ecf0f1; border: 1px solid #5a7b9b; border-radius: 4px;">
                            <option value="" style="background-color: #34495e;">Semua</option>
                            @foreach ($tahunList as $tahun)
                                <option value="{{ $tahun }}"
                                    {{ (string) request('tahun') === (string) $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 🔹 Filter Bulan --}}
                @if ($showBulan && isset($bulanList))
                    <div class="form-group col-md-2 col-sm-6">
                        <label style="color:#ecf0f1; font-weight: 600;">Bulan</label>
                        <select name="bulan" class="form-control"
                            style="background-color: #4a627a; color: #ecf0f1; border: 1px solid #5a7b9b; border-radius: 4px;">
                            <option value="" style="background-color: #34495e;">Semua</option>
                            @foreach ($bulanList as $num => $nama)
                                <option value="{{ $num }}"
                                    {{ (string) request('bulan') === (string) $num ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                {{-- 🔹 Filter Usaha --}}
                @if ($showUsaha && isset($usahaList))
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#ecf0f1; font-weight: 600;">Usaha</label>
                        <select name="usaha_id" class="form-control"
                            style="background-color: #4a627a; color: #ecf0f1; border: 1px solid #5a7b9b; border-radius: 4px;">
                            <option value="" style="background-color: #34495e;">Semua Usaha</option>
                            @foreach ($usahaList as $usaha)
                                <option value="{{ $usaha->id }}"
                                    {{ (string) request('usaha_id') === (string) $usaha->id ? 'selected' : '' }}>
                                    {{ $usaha->nama_usaha }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 🔹 Filter Kategori --}}
                @if ($showKategori && isset($kategoriList))
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#ecf0f1; font-weight: 600;">Kategori</label>
                        <select name="kategori_id" class="form-control"
                            style="background-color: #4a627a; color: #ecf0f1; border: 1px solid #5a7b9b; border-radius: 4px;">
                            <option value="" style="background-color: #34495e;">Semua</option>
                            @foreach ($kategoriList as $kategori)
                                <option value="{{ $kategori->id }}"
                                    {{ (string) request('kategori_id') === (string) $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 🔹 Filter Status (buat halaman transaksi) --}}
                @if ($showStatus && isset($statusList))
                    <div class="form-group col-md-2 col-sm-6">
                        <label style="color:#ecf0f1; font-weight: 600;">Status</label>
                        <select name="status" class="form-control"
                            style="background-color: #4a627a; color: #ecf0f1; border: 1px solid #5a7b9b; border-radius: 4px;">
                            <option value="" style="background-color: #34495e;">Semua</option>
                            @foreach ($statusList as $status)
                                <option value="{{ $status }}"
                                    {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 🔹 Filter Pengerajin (admin: semua pengerajin) --}}
                @if ($showPengerajin && isset($pengerajinList) && $pengerajinList->count())
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#ecf0f1; font-weight: 600;">Pengerajin</label>
                        <select name="pengerajin_id" class="form-control"
                            style="background-color: #4a627a; color: #ecf0f1; border: 1px solid #5a7b9b; border-radius: 4px;">
                            <option value="" style="background-color: #34495e;">Semua Pengerajin</option>
                            @foreach ($pengerajinList as $p)
                                <option value="{{ $p->id }}"
                                    {{ (string) request('pengerajin_id') === (string) $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_pengerajin }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 🔹 Range tanggal biasa
                @if ($showDateRange)
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#ecf0f1; font-weight: 600;">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control"
                            style="background-color: #4a627a; color: #ecf0f1; border: 1px solid #5a7b9b; border-radius: 4px;"
                            value="{{ request('start_date') }}">
                    </div>

                    <div class="form-group col-md-2 col-sm-6">
                        <label style="color:#ecf0f1; font-weight: 600;">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control"
                            style="background-color: #4a627a; color: #ecf0f1; border: 1px solid #5a7b9b; border-radius: 4px;"
                            value="{{ request('end_date') }}">
                    </div>
                @endif
            </div> --}}

            {{-- 🔹 Filter Periode (day / week / month / year) (Admin only) --}}
            @if ($showPeriode)
                @include('pengerajin.laporan_usaha.partials.filter_periode')
            @endif

            {{-- Tombol Terapkan, PDF, Export, Reset --}}
            <div class="col-12 d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary mr-2">
                    <i class="fas fa-filter"></i> Terapkan
                </button>

                @isset($pdfRoute)
                    <a href="{{ route($pdfRoute, request()->query()) }}" class="btn btn-danger mr-2">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                @endisset

                @isset($exportRoute)
                    <a href="{{ route($exportRoute, request()->query()) }}" class="btn btn-success mr-2">
                        <i class="fas fa-file-export"></i> Export
                    </a>
                @endisset
                <a href="{{ $resetUrl }}" class="btn btn-secondary mr-2">

                <a href="{{ $resetUrl }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>

        </form>
    </div>
</div>
