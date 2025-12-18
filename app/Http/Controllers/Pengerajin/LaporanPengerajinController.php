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
            ->when($selectedUsahaId, function ($q) use ($selectedUsahaId) {
                $q->where('up.usaha_id', $selectedUsahaId);
            }, function ($q) use ($usahaIds) {
                $q->whereIn('up.usaha_id', $usahaIds);
            })
            ->select('p.id', 'p.nama_pengerajin')
            ->distinct()
            ->orderBy('p.nama_pengerajin')
            ->get();
    }

    /**
     * Baca pilihan "Pengerajin" di filter:
     * - Kalau dipilih id tertentu → pakai itu
     * - Kalau klik "Terapkan" tapi kosong → artinya "semua pengerajin"
     * - Kalau halaman baru dibuka tanpa apply_filters → default ke pengerajin login
     */
    protected function resolveFilterPengerajinId(Request $request, int $loginPengerajinId, array $usahaIds): ?int
    {
        if ($request->filled('pengerajin_id')) {
            $id = (int) $request->pengerajin_id;

            $allowed = DB::table('usaha_pengerajin')
                ->whereIn('usaha_id', $usahaIds)
                ->where('pengerajin_id', $id)
                ->exists();

            if (!$allowed) {
                abort(403, 'Pengerajin tidak valid untuk usaha ini.');
            }

            return $id;
        }

        // Kalau user klik "Terapkan" tapi dropdown kosong → tampilkan semua
        if ($request->has('apply_filters')) {
            return null;
        }

        // Default: hanya data pengerajin yang login
        $request->merge(['pengerajin_id' => $loginPengerajinId]);
        return $loginPengerajinId;
    }

    protected function applyPengerajinFilter(Builder $q, ?int $filterPengerajinId, string $pAlias = 'p'): Builder
    {
        if (!$filterPengerajinId) {
            return $q;
        }

        return $q->where("$pAlias.pengerajin_id", $filterPengerajinId);
    }

    // =========================================================================
    // HELPER TANGGAL
    // =========================================================================
    protected function resolveDateRange(Request $request, ?int $defaultLastDays = null): array
    {
        $periodeType = $request->input('periode_type');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

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
                        // abaikan, jatuh ke fallback
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

        // Kalau semuanya kosong & ada defaultLastDays → pakai X hari terakhir
        if (!$startCarbon && !$endCarbon && $defaultLastDays) {
            $endCarbon = Carbon::now()->endOfDay();
            $startCarbon = $endCarbon->copy()->subDays($defaultLastDays - 1)->startOfDay();
        }

        return [$startCarbon, $endCarbon];
    }

    // =========================================================================
    // BASE QUERY UTAMA
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

        // Filter by pengerajin (login / rekan / semua)
        $base = $this->applyPengerajinFilter($base, $filterPengerajinId, 'p');

        // Filter tanggal
        if ($start) {
            $base->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $base->whereDate('o.created_at', '<=', $end);
        }

        // Tambahan filter "tahun / bulan" kalau dipakai
        if ($request->filled('tahun')) {
            $base->whereYear('o.created_at', $request->tahun);
        }
        if ($request->filled('bulan')) {
            $base->whereMonth('o.created_at', $request->bulan);
        }

        // Filter kategori & usaha
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
            12 => 'Desember'
        ];

        // --- list usaha & kategori ---
        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();

        // user pembeli (role guest) hanya buat referensi lain kalau perlu
        $userList = User::where('role', 'guest')->get();

        // list pengerajin rekan (untuk dropdown filter pengerajin)
        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

        // --- RANGE TANGGAL DARI FILTER PERIODE ---
        $hasSimplePeriode = $request->filled('tahun') || $request->filled('bulan');
        [$startDate, $endDate] = $this->resolveDateRange($request, $hasSimplePeriode ? null : 30);

        $periodeLabel = null;
        if ($startDate && $endDate) {
            if ($startDate->isSameDay($endDate)) {
                $periodeLabel = $startDate->format('d/m/Y');
            } else {
                $periodeLabel = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
            }
        }

        // --- BASE QUERY UTAMA ---
        $base = $this->baseQueryPengerajin($request, $loginPengerajinId, $usahaIds, $filterPengerajinId, $startDate, $endDate);
        $baseQuery = clone $base;

        // --- METRIC ATAS ---
        $totalTransaksi = (clone $baseQuery)->distinct('o.id')->count('o.id');

        $totalPendapatan = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(oi.quantity * oi.price_at_purchase),0) as total')
            ->value('total') ?? 0;

        // --- PENDAPATAN PER USAHA (TOP 3) ---
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

        // --- PERFORMA PENJUALAN 5 USAHA TERATAS (LINE CHART) ---
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

        // --- TOP PRODUK ---
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

        // --- TOP USER (PEMBELI) ---
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

        // --- TOP KATEGORI ---
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

        // --- PRODUK FAVORITE (LIKE) ---
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

        // --- PRODUK VIEWS ---
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
