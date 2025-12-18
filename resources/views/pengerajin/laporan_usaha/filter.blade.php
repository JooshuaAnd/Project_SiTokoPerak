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
    $showPeriode = $showPeriode ?? true;
    $showPengerajin = $showPengerajin ?? true;
@endphp
<div class="card card-modern mb-3">
    <div class="card-body">
        <form method="GET" action="{{ $action }}">
            <div class="row">
                {{-- 🔹 Filter Tahun
                @if ($showTahun && isset($tahunList))
                    <div class="form-group col-md-2 col-sm-6">
                        <label style="color:#b8ccdf;">Tahun</label>
                        <select name="tahun" class="form-control">
                            <option value="">Semua</option>
                            @foreach ($tahunList as $tahun)
                                <option value="{{ $tahun }}"
                                    {{ (string) request('tahun') === (string) $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif --}}

                {{-- 🔹 Filter Bulan
                @if ($showBulan && isset($bulanList))
                    <div class="form-group col-md-2 col-sm-6">
                        <label style="color:#b8ccdf;">Bulan</label>
                        <select name="bulan" class="form-control">
                            <option value="">Semua</option>
                            @foreach ($bulanList as $num => $nama)
                                <option value="{{ $num }}"
                                    {{ (string) request('bulan') === (string) $num ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif --}}

                {{-- 🔹 Filter Usaha --}}
                @if ($showUsaha && isset($usahaList))
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
                @endif

                {{-- 🔹 Filter Kategori --}}
                @if ($showKategori && isset($kategoriList))
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Kategori</label>
                        <select name="kategori_id" class="form-control">
                            <option value="">Semua</option>
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
                        <label style="color:#b8ccdf;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua</option>
                            @foreach ($statusList as $status)
                                <option value="{{ $status }}"
                                    {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 🔹 Filter Pengerajin (rekan satu toko) --}}
                @if ($showPengerajin && isset($pengerajinList) && $pengerajinList->count())
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Pengerajin</label>
                        <select name="pengerajin_id" class="form-control">
                            <option value="">Semua Pengerajin</option>
                            @foreach ($pengerajinList as $u)
                                <option value="{{ $u->id }}"
                                    {{ (string) request('pengerajin_id') === (string) $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_pengerajin }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                {{-- 🔹 Range tanggal biasa
                @if ($showDateRange)
                    <div class="form-group col-md-3 col-sm-6">
                        <label style="color:#b8ccdf;">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control"
                            value="{{ request('start_date') }}">
                    </div>

                    <div class="form-group col-md-2 col-sm-6">
                        <label style="color:#b8ccdf;">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                @endif
            </div> --}}

            {{-- 🔹 Filter Periode (day / week / month / year) --}}
            @if ($showPeriode)
                @include('pengerajin.laporan_usaha.partials.filter_periode')
            @endif

            {{-- 🔹 Tombol --}}
            <div class="row mt-2">
                <div class="form-group col-md-4 col-sm-12" style="margin-top: 4px;">
                    <div class="row">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block mb-2" name="apply_filters" value="1">
                                <i class="fa fa-filter"></i> Terapkan
                            </button>
                        </div>
                        <div class="col-6">
                            <a href="{{ $resetUrl }}" class="btn btn-secondary btn-block mb-2">
                                <i class="fa fa-sync-alt"></i> Reset
                            </a>
                        </div>
                    </div>

                    {{-- 🔹 Tombol Export (opsional) --}}
                    @isset($exportRoute)
                        <a href="{{ route($exportRoute, request()->query()) }}" class="btn btn-success btn-block mt-2">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </a>
                    @endisset
                </div>
            </div>
        </form>
    </div>
</div>
