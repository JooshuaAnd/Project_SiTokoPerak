<?php

namespace App\Http\Controllers\Pengerajin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Models\Usaha;
use App\Models\KategoriProduk;
use App\Models\Pengerajin;
use App\Models\User;

use Illuminate\Database\Query\Builder;

class LaporanPengerajinController extends Controller
{
    // =========================================================================
    // HELPER OTENTIKASI & KONTEKS
    // =========================================================================
    protected function getPengerajinContext(): array
    {
        $userId = Auth::id();
        if (!Auth::check() || !$userId) {
            abort(401, 'Silakan login.');
        }

        $pengerajin = Pengerajin::where('user_id', $userId)->first();
        if (!$pengerajin) {
            abort(403, 'Akses ditolak. Akun Anda tidak terdaftar sebagai Pengerajin aktif.');
        }

        $pengerajinId = (int) $pengerajin->id;

        // mapping lewat tabel pivot usaha_pengerajin
        $usahaIds = DB::table('usaha_pengerajin')
            ->where('pengerajin_id', $pengerajinId)
            ->pluck('usaha_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        if (empty($usahaIds)) {
            abort(403, 'Akses ditolak. Pengerajin ini belum dipetakan ke usaha manapun.');
        }

        return [$pengerajinId, $usahaIds];
    }

    protected function getPengerajinListForFilter(array $usahaIds, ?int $selectedUsahaId = null)
    {
        return DB::table('pengerajin as p')
            ->join('usaha_pengerajin as up', 'up.pengerajin_id', '=', 'p.id')
            ->when(
                $selectedUsahaId,
                fn($q) => $q->where('up.usaha_id', $selectedUsahaId),
                fn($q) => $q->whereIn('up.usaha_id', $usahaIds)
            )
            ->select('p.id', 'p.nama_pengerajin')
            ->distinct()
            ->orderBy('p.nama_pengerajin')
            ->get();
    }

    /**
     * Logika filter pengerajin:
     * - Kalau field `pengerajin_id` ADA di request:
     *     - "" / null  -> user pilih "Semua Pengerajin"  => return null (tanpa WHERE)
     *     - angka      -> validasi ke usaha_pengerajin   => return id
     * - Kalau TIDAK ADA field `pengerajin_id` (halaman baru dibuka):
     *     -> default ke pengerajin yang login
     */
    protected function resolveFilterPengerajinId(Request $request, int $loginPengerajinId, array $usahaIds): ?int
    {
        // Kalau form sudah pernah di-submit, selalu ada field pengerajin_id (bisa kosong)
        if ($request->has('pengerajin_id')) {
            $raw = $request->input('pengerajin_id');

            // Pilih "Semua Pengerajin"
            if ($raw === null || $raw === '') {
                return null;
            }

            $id = (int) $raw;

            $allowed = DB::table('usaha_pengerajin')
                ->whereIn('usaha_id', $usahaIds)
                ->where('pengerajin_id', $id)
                ->exists();

            if (!$allowed) {
                abort(403, 'Pengerajin tidak valid untuk usaha ini.');
            }

            return $id;
        }

        // (fallback) kalau entah kenapa ada apply_filters tapi nggak ada field pengerajin_id → anggap semua
        if ($request->has('apply_filters')) {
            return null;
        }

        // Field belum ada sama sekali ⇒ halaman baru dibuka ⇒ default ke pengerajin login
        $request->merge(['pengerajin_id' => $loginPengerajinId]);

        return $loginPengerajinId;
    }

    protected function applyPengerajinFilter(Builder $q, ?int $filterPengerajinId, string $pAlias = 'p'): Builder
    {
        if (!$filterPengerajinId) {
            // semua pengerajin (sesuai usahaIds)
            return $q;
        }

        return $q->where("$pAlias.pengerajin_id", $filterPengerajinId);
    }

    // =========================================================================
    // HELPER TANGGAL (SAMA DENGAN EXPORT\PengerajinLaporanController)
    // =========================================================================
    protected function resolveDateRange(Request $request, ?int $defaultLastDays = null): array
    {
        $periodeType = $request->input('periode_type');

        // support 2 nama field:
        // - start_date / end_date          (lama)
        // - periode_tentu_start / _end     (baru, di row "Periode Tertentu")
        $start = $request->input('periode_tentu_start', $request->input('start_date'));
        $end = $request->input('periode_tentu_end', $request->input('end_date'));

        $startCarbon = $start ? Carbon::parse($start)->startOfDay() : null;
        $endCarbon = $end ? Carbon::parse($end)->endOfDay() : null;

        switch ($periodeType) {
            case 'day':
                if ($request->filled('periode_day')) {
                    $day = Carbon::parse($request->input('periode_day'));
                    $startCarbon = $day->copy()->startOfDay();
                    $endCarbon = $day->copy()->endOfDay();
                }
                break;

            case 'week':
                if ($request->filled('periode_week')) {
                    try {
                        [$year, $week] = explode('-W', $request->input('periode_week'));
                        $tmp = Carbon::now()->setISODate((int) $year, (int) $week);
                        $startCarbon = $tmp->copy()->startOfDay();
                        $endCarbon = $tmp->copy()->endOfWeek();
                    } catch (\Throwable $e) {
                        // biarkan jatuh ke nilai sebelumnya
                    }
                }
                break;

            case 'month':
                if ($request->filled('periode_year') && $request->filled('periode_month')) {
                    $startCarbon = Carbon::createFromDate(
                        (int) $request->input('periode_year'),
                        (int) $request->input('periode_month'),
                        1
                    )->startOfDay();
                    $endCarbon = $startCarbon->copy()->endOfMonth();
                }
                break;

            case 'year':
                if ($request->filled('periode_year')) {
                    $year = (int) $request->input('periode_year');
                    $startCarbon = Carbon::createFromDate($year, 1, 1)->startOfDay();
                    $endCarbon = Carbon::createFromDate($year, 12, 31)->endOfDay();
                }
                break;
        }

        // Default X hari terakhir (misal dashboard/slow-moving)
        if (!$startCarbon && !$endCarbon && $defaultLastDays) {
            $endCarbon = Carbon::now()->endOfDay();
            $startCarbon = $endCarbon->copy()->subDays($defaultLastDays - 1)->startOfDay();
        }

        return [$startCarbon, $endCarbon];
    }

    // =========================================================================
    // BASE QUERY UTAMA (dipakai semua widget dashboard)
    // =========================================================================
    protected function baseQueryPengerajin(
        Request $request,
        int $loginPengerajinId,
        array $usahaIds,
        ?int $filterPengerajinId,
        ?Carbon $start,
        ?Carbon $end
    ): Builder {
        $base = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('produk as p', 'p.id', '=', 'oi.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->join('usaha_pengerajin as map', 'map.pengerajin_id', '=', 'p.pengerajin_id')
            ->join('usaha as us', 'us.id', '=', 'map.usaha_id')
            ->whereIn('map.usaha_id', $usahaIds);

        // filter pengerajin (login / rekan / semua)
        $base = $this->applyPengerajinFilter($base, $filterPengerajinId, 'p');

        // filter tanggal
        if ($start) {
            $base->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $base->whereDate('o.created_at', '<=', $end);
        }

        // filter tambahan tahun / bulan (kalau memang dipakai di form)
        if ($request->filled('tahun')) {
            $base->whereYear('o.created_at', $request->tahun);
        }
        if ($request->filled('bulan')) {
            $base->whereMonth('o.created_at', $request->bulan);
        }

        // filter kategori & usaha
        if ($request->filled('kategori_id')) {
            $base->where('k.id', $request->kategori_id);
        }
        if ($request->filled('usaha_id')) {
            $base->where('us.id', $request->usaha_id);
        }

        return $base;
    }

    // =========================================================================
    // DASHBOARD UTAMA
    // =========================================================================
    public function index(Request $request)
    {
        // --- konteks pengerajin & usaha yang boleh dilihat ---
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        // --- list tahun / bulan untuk dropdown ---
        $currentYear = now()->year;
        $tahunList = range($currentYear - 5, $currentYear);
        rsort($tahunList);

        $bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // --- list usaha, kategori, dll ---
        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $userList = User::where('role', 'guest')->get(); // cuma referensi kalau perlu

        // dropdown "Pengerajin" → semua rekan dalam usaha-usaha ini
        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

        // --- RANGE TANGGAL DARI FILTER PERIODE ---
        // CHANGED: disamakan dengan Export\PengerajinLaporanController
        // Tidak lagi pakai default 30 hari; full pakai periode yang dipilih user
        [$startDate, $endDate] = $this->resolveDateRange($request, null);

        // label untuk ditampilkan di card
        $periodeLabel = null;
        if ($startDate && $endDate) {
            if ($startDate->isSameDay($endDate)) {
                $periodeLabel = $startDate->format('d/m/Y');
            } else {
                $periodeLabel = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
            }
        }

        // --- BASE QUERY UTAMA ---
        $base = $this->baseQueryPengerajin(
            $request,
            $loginPengerajinId,
            $usahaIds,
            $filterPengerajinId,
            $startDate,
            $endDate
        );
        $baseQuery = clone $base;

        // ---------------------------------------------------------------------
        // METRIC ATAS
        // ---------------------------------------------------------------------
        $totalTransaksi = (clone $baseQuery)->distinct('o.id')->count('o.id');

        $totalPendapatan = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(oi.quantity * oi.price_at_purchase),0) as total')
            ->value('total') ?? 0;

        // ---------------------------------------------------------------------
        // PENDAPATAN PER USAHA (TOP 3)
        // ---------------------------------------------------------------------
        $pendapatanPerUsaha = (clone $baseQuery)
            ->selectRaw('us.id as usaha_id, us.nama_usaha, SUM(oi.quantity * oi.price_at_purchase) as total')
            ->groupBy('us.id', 'us.nama_usaha')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $pendapatanChart = [
            'labels' => $pendapatanPerUsaha->pluck('nama_usaha'),
            'data' => $pendapatanPerUsaha->pluck('total'),
        ];

        // ---------------------------------------------------------------------
        // PERFORMA PENJUALAN (LINE CHART, MAX 5 USAHA TERATAS)
        // ---------------------------------------------------------------------
        $performaPenjualanChart = [
            'labels' => [],
            'datasets' => [],
        ];

        $topUsahaPerforma = (clone $baseQuery)
            ->selectRaw('us.id as usaha_id, us.nama_usaha, SUM(oi.quantity * oi.price_at_purchase) as total')
            ->groupBy('us.id', 'us.nama_usaha')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        if ($topUsahaPerforma->count() > 0) {
            $topUsahaIds = $topUsahaPerforma->pluck('usaha_id')->all();

            $performaRaw = (clone $baseQuery)
                ->whereIn('us.id', $topUsahaIds)
                ->selectRaw('
                    us.id as usaha_id,
                    us.nama_usaha,
                    DATE(o.created_at) as tgl,
                    SUM(oi.quantity * oi.price_at_purchase) as total
                ')
                ->groupBy('us.id', 'us.nama_usaha', 'tgl')
                ->orderBy('tgl')
                ->get();

            $labels = $performaRaw->pluck('tgl')->unique()->sort()->values()->all();
            $datasets = [];

            foreach ($topUsahaPerforma as $usaha) {
                $dataPerUsaha = [];
                foreach ($labels as $tgl) {
                    $row = $performaRaw->first(
                        fn($item) =>
                        $item->usaha_id == $usaha->usaha_id && $item->tgl === $tgl
                    );
                    $dataPerUsaha[] = $row ? (float) $row->total : 0;
                }

                $datasets[] = [
                    'label' => $usaha->nama_usaha,
                    'data' => $dataPerUsaha,
                ];
            }

            $performaPenjualanChart = [
                'labels' => $labels,
                'datasets' => $datasets,
            ];
        }

        // ---------------------------------------------------------------------
        // TOP PRODUK
        // ---------------------------------------------------------------------
        $topProdukRow = (clone $baseQuery)
            ->selectRaw('p.nama_produk, SUM(oi.quantity) as total_qty')
            ->groupBy('p.id', 'p.nama_produk')
            ->orderByDesc('total_qty')
            ->first();

        $topProduk = $topProdukRow->nama_produk ?? null;

        $produkTerlaris = (clone $baseQuery)
            ->selectRaw('p.nama_produk, SUM(oi.quantity) as total_qty')
            ->groupBy('p.id', 'p.nama_produk')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        $produkTerlarisChart = [
            'labels' => $produkTerlaris->pluck('nama_produk'),
            'data' => $produkTerlaris->pluck('total_qty'),
        ];

        // ---------------------------------------------------------------------
        // TOP USER (PEMBELI)
        // ---------------------------------------------------------------------
        $userAktifRow = (clone $baseQuery)
            ->whereNotNull('u.id')
            ->selectRaw('u.username, COUNT(DISTINCT o.id) as total_transaksi')
            ->groupBy('u.id', 'u.username')
            ->orderByDesc('total_transaksi')
            ->first();

        $userAktif = $userAktifRow->username ?? null;

        $userAktifList = (clone $baseQuery)
            ->whereNotNull('u.id')
            ->selectRaw('u.username, COUNT(DISTINCT o.id) as total_transaksi')
            ->groupBy('u.id', 'u.username')
            ->orderByDesc('total_transaksi')
            ->limit(3)
            ->get();

        $transaksiUserChart = [
            'labels' => $userAktifList->pluck('username'),
            'data' => $userAktifList->pluck('total_transaksi'),
        ];

        // ---------------------------------------------------------------------
        // TOP KATEGORI
        // ---------------------------------------------------------------------
        $kategoriTerjual = (clone $baseQuery)
            ->selectRaw('k.nama_kategori_produk, SUM(oi.quantity) as total_qty')
            ->groupBy('k.id', 'k.nama_kategori_produk')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        $kategoriChart = [
            'labels' => $kategoriTerjual->pluck('nama_kategori_produk'),
            'data' => $kategoriTerjual->pluck('total_qty'),
        ];

        // ---------------------------------------------------------------------
        // PRODUK FAVORITE (LIKE)
        // ---------------------------------------------------------------------
        $produkFavoriteQ = DB::table('produk as p')
            ->join('usaha_pengerajin as map', 'map.pengerajin_id', '=', 'p.pengerajin_id')
            ->join('usaha as us', 'us.id', '=', 'map.usaha_id')
            ->leftJoin('produk_likes as pl', 'pl.produk_id', '=', 'p.id')
            ->whereIn('map.usaha_id', $usahaIds);

        $produkFavoriteQ = $this->applyPengerajinFilter($produkFavoriteQ, $filterPengerajinId, 'p');

        if ($request->filled('usaha_id')) {
            $produkFavoriteQ->where('us.id', $request->usaha_id);
        }
        if ($startDate) {
            $produkFavoriteQ->whereDate('pl.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $produkFavoriteQ->whereDate('pl.created_at', '<=', $endDate);
        }

        $produkFavorite = $produkFavoriteQ
            ->selectRaw('p.nama_produk, COUNT(DISTINCT pl.id) as total_like')
            ->groupBy('p.id', 'p.nama_produk')
            ->orderByDesc('total_like')
            ->limit(3)
            ->get();

        $produkFavoriteChart = [
            'labels' => $produkFavorite->pluck('nama_produk'),
            'data' => $produkFavorite->pluck('total_like'),
        ];

        // ---------------------------------------------------------------------
        // PRODUK VIEWS
        // ---------------------------------------------------------------------
        $produkViewsQ = DB::table('produk as p')
            ->join('usaha_pengerajin as map', 'map.pengerajin_id', '=', 'p.pengerajin_id')
            ->join('usaha as us', 'us.id', '=', 'map.usaha_id')
            ->leftJoin('produk_views as pv', 'pv.produk_id', '=', 'p.id')
            ->whereIn('map.usaha_id', $usahaIds);

        $produkViewsQ = $this->applyPengerajinFilter($produkViewsQ, $filterPengerajinId, 'p');

        if ($request->filled('usaha_id')) {
            $produkViewsQ->where('us.id', $request->usaha_id);
        }
        if ($startDate) {
            $produkViewsQ->whereDate('pv.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $produkViewsQ->whereDate('pv.created_at', '<=', $endDate);
        }

        $produkViews = $produkViewsQ
            ->selectRaw('p.nama_produk, COUNT(DISTINCT pv.id) as total_view')
            ->groupBy('p.id', 'p.nama_produk')
            ->orderByDesc('total_view')
            ->limit(3)
            ->get();

        $produkViewChart = [
            'labels' => $produkViews->pluck('nama_produk'),
            'data' => $produkViews->pluck('total_view'),
        ];

        return view('pengerajin.laporan_usaha.laporan', compact(
            'tahunList',
            'bulanList',
            'usahaList',
            'kategoriList',
            'totalTransaksi',
            'totalPendapatan',
            'pendapatanChart',
            'produkTerlarisChart',
            'produkFavoriteChart',
            'produkViewChart',
            'transaksiUserChart',
            'kategoriChart',
            'topProduk',
            'userAktif',
            'pengerajinList',
            'periodeLabel',
            'performaPenjualanChart'
        ));
    }
}
