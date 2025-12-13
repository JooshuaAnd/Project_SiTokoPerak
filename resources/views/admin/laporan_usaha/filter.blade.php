{{-- === FILTER LAPORAN === --}}
<div class="card shadow-sm p-4" style="border-radius: 14px; margin-bottom: 20px;">
    <h5 class="mb-3 fw-bold">Filter Laporan</h5>

    <form method="GET" action="{{ route('admin.laporan.index') }}">
        <div class="row g-3">

            {{-- Bulan --}}
            <div class="col-md-2">
                <label class="form-label fw-bold">Bulan</label>
                <select name="bulan" class="form-control">
                    <option value="">Semua</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tahun --}}
            <div class="col-md-2">
                <label class="form-label fw-bold">Tahun</label>
                <select name="tahun" class="form-control">
                    <option value="">Semua</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal Mulai --}}
            <div class="col-md-2">
                <label class="form-label fw-bold">Mulai</label>
                <input type="date" name="start" class="form-control" value="{{ request('start') }}">
            </div>

            {{-- Tanggal Akhir --}}
            <div class="col-md-2">
                <label class="form-label fw-bold">Sampai</label>
                <input type="date" name="end" class="form-control" value="{{ request('end') }}">
            </div>

            {{-- Usaha --}}
            <div class="col-md-2">
                <label class="form-label fw-bold">Usaha</label>
                <select name="usaha" class="form-control">
                    <option value="">Semua</option>
                    @foreach($usahaList as $u)
                        <option value="{{ $u->id }}" {{ request('usaha') == $u->id ? 'selected' : '' }}>
                            {{ $u->nama_usaha }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kategori Produk --}}
            <div class="col-md-2">
                <label class="form-label fw-bold">Kategori</label>
                <select name="kategori" class="form-control">
                    <option value="">Semua</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ $k->id }}" {{ request('kategori') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kategori_produk }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- User --}}
            <div class="col-md-2">
                <label class="form-label fw-bold">User</label>
                <select name="user" class="form-control">
                    <option value="">Semua</option>
                    @foreach($userList as $usr)
                        <option value="{{ $usr->id }}" {{ request('user') == $usr->id ? 'selected' : '' }}>
                            {{ $usr->username }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol --}}
            <div class="col-md-12 mt-2 text-end">
                <button class="btn btn-primary px-4">
                    <i class="ni ni-zoom-split-in"></i> Terapkan Filter
                </button>
            </div>

        </div>
    </form>
</div>
