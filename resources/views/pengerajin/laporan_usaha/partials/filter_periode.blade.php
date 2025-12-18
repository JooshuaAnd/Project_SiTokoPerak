{{-- resources/views/admin/laporan_usaha/partials/filter_periode.blade.php --}}

@php
    $currentYear = now()->year;
    $startYearList = $currentYear - 5; // 5 tahun kebelakang, silakan sesuaikan
    $tahunList = range($startYearList, $currentYear);
    rsort($tahunList);

    $selectedYear = request('periode_year') ?? $currentYear;
    $selectedMonth = request('periode_month');
    $periodeType = request('periode_type');
@endphp

<div class="row mt-2">
    <div class="col-12">
        <hr style="border-color:rgba(255,255,255,0.08);margin:8px 0 14px;">
        <span style="color:#b8ccdf; font-size:13px; opacity:.9;">
            <i class="fas fa-calendar-alt"></i> Opsi filter periode (opsional). Kalau dikosongkan, sistem pakai tanggal
            <strong>Mulai / Akhir</strong> biasa.
        </span>
    </div>

    <div class="col-12 d-flex flex-wrap align-items-end">
        {{-- Jenis Periode --}}
        <div class="form-group col-md-3 col-sm-6 mt-2" style="margin-top: 10px !important;">
            <label style="color:#b8ccdf;">Jenis Periode</label>
            <select name="periode_type" id="periode_type" class="form-control">
                <option value="" {{ $periodeType == null ? 'selected' : '' }}>Custom (Tanggal)</option>
                <option value="day" {{ $periodeType == 'day' ? 'selected' : '' }}>Per Hari</option>
                <option value="week" {{ $periodeType == 'week' ? 'selected' : '' }}>Per Minggu</option>
                <option value="month" {{ $periodeType == 'month' ? 'selected' : '' }}>Per Bulan</option>
                <option value="year" {{ $periodeType == 'year' ? 'selected' : '' }}>Per Tahun</option>
            </select>
        </div>

        {{-- Per Hari --}}
        <div class="form-group col-md-3 col-sm-6 mt-2 periode-input periode-day d-none" style="margin-top: 10px !important;">
            <label style="color:#b8ccdf;">Tanggal (Hari)</label>
            <input type="date" name="periode_day" class="form-control" value="{{ request('periode_day') }}">
        </div>

    {{-- Per Minggu --}}
    <div class="form-group col-md-3 col-sm-6 mt-2 periode-input periode-week d-none" style="margin-top: 10px !important;">
        <label style="color:#b8ccdf;">Minggu (ISO Week)</label>
        {{-- format: YYYY-Www  contoh: 2025-W09 --}}
        <input type="week" name="periode_week" class="form-control" value="{{ request('periode_week') }}">
    </div>

    {{-- Per Bulan --}}
    <div class="form-group col-md-3 col-sm-6 mt-2 periode-input periode-month d-none" style="margin-top: 10px !important;">
        <label style="color:#b8ccdf;">Bulan & Tahun</label>
        <div class="d-flex">
            <select name="periode_month" class="form-control mr-1">
                <option value="">Bulan</option>
                @foreach ([
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ] as $val => $label)
                    <option value="{{ $val }}" {{ (int) $selectedMonth === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <select name="periode_year" class="form-control ml-1">
                <option value="">Tahun</option>
                @foreach ($tahunList as $tahun)
                    <option value="{{ $tahun }}" {{ (int) $selectedYear === (int) $tahun ? 'selected' : '' }}>
                        {{ $tahun }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Per Tahun --}}
    <div class="form-group col-md-3 col-sm-6 mt-2 periode-input periode-year d-none" style="margin-top: 10px !important;">
        <label style="color:#b8ccdf;">Tahun</label>
        <select name="periode_year" class="form-control">
            <option value="">Pilih Tahun</option>
            @foreach ($tahunList as $tahun)
                <option value="{{ $tahun }}" {{ (int) $selectedYear === (int) $tahun ? 'selected' : '' }}>
                    {{ $tahun }}
                </option>
            @endforeach
        </select>
    </div>
    </div>
</div>

{{-- Script show/hide input periode --}}
<script>
    (function() {
        const select = document.getElementById('periode_type');
        if (!select) return;

        const dayField = document.querySelector('.periode-day');
        const weekField = document.querySelector('.periode-week');
        const monthField = document.querySelector('.periode-month');
        const yearField = document.querySelector('.periode-year');

        function togglePeriodeFields() {
            const val = select.value;

            [dayField, weekField, monthField, yearField].forEach(el => {
                if (!el) return;
                el.classList.add('d-none');
            });

            if (val === 'day' && dayField) dayField.classList.remove('d-none');
            if (val === 'week' && weekField) weekField.classList.remove('d-none');
            if (val === 'month' && monthField) monthField.classList.remove('d-none');
            if (val === 'year' && yearField) yearField.classList.remove('d-none');
        }

        select.addEventListener('change', togglePeriodeFields);
        // supaya pas reload page tetap sesuai pilihan sebelumnya
        togglePeriodeFields();
    })();
</script>


