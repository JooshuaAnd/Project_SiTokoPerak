<?php

namespace App\Http\Controllers\Pengerajin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use App\Models\Usaha;
use App\Models\KategoriProduk;
use App\Models\Pengerajin;
use App\Models\User;

use Illuminate\Database\Query\Builder;

class LaporanPengerajinController extends Controller
{
    // =========================================================================
    // HELPER OTENTIKASI & KONTEKS (mapping lewat usaha_pengerajin)
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

    /**
     * List pengerajin untuk dropdown filter (hanya rekan satu usaha)
     */
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

    // =========================================================================
    // HELPER: RESOLVE FILTER PENGERAJIN (default login, tapi bisa "Semua")
    // =========================================================================
    protected function resolveFilterPengerajinId(Request $request, int $loginPengerajinId, array $usahaIds): ?int
    {
        // Kalau dropdown dipilih
        if ($request->filled('pengerajin_id')) {
            $id = (int) $request->pengerajin_id;

            // Validasi: pengerajin ini rekan dalam usaha yang boleh diakses
            $allowed = DB::table('usaha_pengerajin')
                ->whereIn('usaha_id', $usahaIds)
                ->where('pengerajin_id', $id)
                ->exists();

            if (!$allowed)
                abort(403, 'Pengerajin tidak valid untuk usaha ini.');

            return $id;
        }

        // Kalau user klik Terapkan tapi pilih "Semua Pengerajin" -> NULL (SEMUA)
        if ($request->has('apply_filters')) {
            return null;
        }

        // Default pertama kali buka halaman -> login
        // biar dropdown juga nyantol ke login (tanpa edit blade)
        $request->merge(['pengerajin_id' => $loginPengerajinId]);

        return $loginPengerajinId;
    }

    /**
     * Apply filter pengerajin ke query:
     * - kalau ada usaha_produk.pengerajin_id -> pakai itu
     * - else kalau ada produk.pengerajin_id -> pakai itu
     * - else fallback join usaha_pengerajin (WARNING: bisa tidak membedakan data per pengerajin dalam 1 usaha)
     */
    protected function applyPengerajinFilter(Builder $q, ?int $filterPengerajinId, string $upAlias, string $pAlias): Builder
    {
        if (!$filterPengerajinId)
            return $q;

        if (Schema::hasColumn('usaha_produk', 'pengerajin_id')) {
            return $q->where("$upAlias.pengerajin_id", $filterPengerajinId);
        }

        if (Schema::hasColumn('produk', 'pengerajin_id')) {
            return $q->where("$pAlias.pengerajin_id", $filterPengerajinId);
        }

        // fallback (tidak ideal)
        return $q->join('usaha_pengerajin as map', function ($join) use ($filterPengerajinId, $upAlias) {
            $join->on('map.usaha_id', '=', "$upAlias.usaha_id")
                ->where('map.pengerajin_id', '=', $filterPengerajinId);
        });
    }

    // =========================================================================
    // HELPER TANGGAL (resolveDateRange)
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
                    $weekInput = $request->input('periode_week'); // contoh: "2025-W09"
                    try {
                        [$year, $week] = explode('-W', $weekInput);
                        $tmp = Carbon::now()->setISODate((int) $year, (int) $week);
                        $startCarbon = $tmp->copy()->startOfDay();
                        $endCarbon = $tmp->copy()->endOfWeek();
                    } catch (\Throwable $e) {
                        // fallback
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

        // Default N hari terakhir kalau tidak ada filter tanggal
        if (!$startCarbon && !$endCarbon && $defaultLastDays) {
            $endCarbon = Carbon::now()->endOfDay();
            $startCarbon = $endCarbon->copy()->subDays($defaultLastDays - 1)->startOfDay();
        }

        return [$startCarbon, $endCarbon];
    }

    // =========================================================================
    // BASE QUERY UTAMA (Dashboard Index)
    // =========================================================================
    protected function baseQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $hasSimplePeriode = $request->filled('tahun') || $request->filled('bulan');
        [$start, $end] = $this->resolveDateRange($request, $hasSimplePeriode ? null : 30);

        $base = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('usaha_produk', 'order_items.usaha_produk_id', '=', 'usaha_produk.id')
            ->join('produk', 'usaha_produk.produk_id', '=', 'produk.id')
            ->leftJoin('kategori_produk', 'produk.kategori_produk_id', '=', 'kategori_produk.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->whereIn('usaha_produk.usaha_id', $usahaIds);

        $base = $this->applyPengerajinFilter($base, $filterPengerajinId, 'usaha_produk', 'produk');

        if ($start)
            $base->whereDate('orders.created_at', '>=', $start);
        if ($end)
            $base->whereDate('orders.created_at', '<=', $end);

        if ($request->filled('tahun'))
            $base->whereYear('orders.created_at', $request->tahun);
        if ($request->filled('bulan'))
            $base->whereMonth('orders.created_at', $request->bulan);

        if ($request->filled('kategori_id'))
            $base->where('kategori_produk.id', $request->kategori_id);
        if ($request->filled('usaha_id'))
            $base->where('usaha_produk.usaha_id', $request->usaha_id);

        return $base;
    }

    // =========================================================================
    // DASHBOARD UTAMA
    // =========================================================================
    public function index(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $currentYear = now()->year;
        $startYear = $currentYear - 5;
        $tahunList = range($startYear, $currentYear);
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

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $userList = User::where('role', 'customer')->get(); // kalau kepakai di halaman lain

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

        $base = $this->baseQueryPengerajin($request);
        $baseQuery = clone $base;

        // METRICS
        $totalTransaksi = (clone $baseQuery)->distinct('orders.id')->count('orders.id');
        $totalPendapatan = (clone $baseQuery)
            ->selectRaw('SUM(order_items.quantity * order_items.price_at_purchase) as total')
            ->value('total') ?? 0;

        // PENDAPATAN PER USAHA (Top 3)
        $pendapatanPerUsaha = (clone $baseQuery)
            ->leftJoin('usaha', 'usaha_produk.usaha_id', '=', 'usaha.id')
            ->selectRaw('usaha.nama_usaha, SUM(order_items.quantity * order_items.price_at_purchase) as total')
            ->whereNotNull('usaha.nama_usaha')
            ->groupBy('usaha.id', 'usaha.nama_usaha')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $pendapatanChart = [
            'labels' => $pendapatanPerUsaha->pluck('nama_usaha'),
            'data' => $pendapatanPerUsaha->pluck('total'),
        ];

        // TOP PRODUK (1)
        $topProdukRow = (clone $baseQuery)
            ->selectRaw('produk.nama_produk, SUM(order_items.quantity) as total_qty')
            ->groupBy('produk.id', 'produk.nama_produk')
            ->orderByDesc('total_qty')
            ->first();

        $topProduk = $topProdukRow->nama_produk ?? null;

        // PRODUK TERLARIS (Top 3)
        $produkTerlaris = (clone $baseQuery)
            ->selectRaw('produk.nama_produk, SUM(order_items.quantity) as total_qty')
            ->groupBy('produk.id', 'produk.nama_produk')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        $produkTerlarisChart = [
            'labels' => $produkTerlaris->pluck('nama_produk'),
            'data' => $produkTerlaris->pluck('total_qty'),
        ];

        // TOP USER (Pembeli Aktif)
        $userAktifRow = (clone $baseQuery)
            ->selectRaw('users.username, COUNT(DISTINCT orders.id) as total_transaksi')
            ->groupBy('users.id', 'users.username')
            ->orderByDesc('total_transaksi')
            ->first();

        $userAktif = $userAktifRow->username ?? null;

        $userAktifList = (clone $baseQuery)
            ->selectRaw('users.username, COUNT(DISTINCT orders.id) as total_transaksi')
            ->groupBy('users.id', 'users.username')
            ->orderByDesc('total_transaksi')
            ->limit(3)
            ->get();

        $transaksiUserChart = [
            'labels' => $userAktifList->pluck('username'),
            'data' => $userAktifList->pluck('total_transaksi'),
        ];

        // TOP KATEGORI (Total Terjual)
        $kategoriTerjual = (clone $baseQuery)
            ->selectRaw('kategori_produk.nama_kategori_produk, SUM(order_items.quantity) as total_qty')
            ->groupBy('kategori_produk.id', 'kategori_produk.nama_kategori_produk')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->get();

        $kategoriChart = [
            'labels' => $kategoriTerjual->pluck('nama_kategori_produk'),
            'data' => $kategoriTerjual->pluck('total_qty'),
        ];

        // PRODUK FAVORITE (Top 3) - aman dari duplicate pakai DISTINCT
        $produkFavoriteQ = DB::table('produk as p')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('produk_likes as pl', 'pl.produk_id', '=', 'p.id')
            ->whereIn('up.usaha_id', $usahaIds);

        $produkFavoriteQ = $this->applyPengerajinFilter($produkFavoriteQ, $filterPengerajinId, 'up', 'p');

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

        // PRODUK VIEWS (Top 3) - aman dari duplicate pakai DISTINCT
        $produkViewsQ = DB::table('produk as p')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('produk_views as pv', 'pv.produk_id', '=', 'p.id')
            ->whereIn('up.usaha_id', $usahaIds);

        $produkViewsQ = $this->applyPengerajinFilter($produkViewsQ, $filterPengerajinId, 'up', 'p');

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
            'pengerajinList'
        ));
    }

    // =========================================================================
    // KATEGORI PRODUK (FIXED: jangan pakai p.usaha_id)
    // =========================================================================
    protected function baseKategoriProdukQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('kategori_produk as k')
            ->join('produk as p', 'p.kategori_produk_id', '=', 'k.id')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('order_items as oi', 'oi.usaha_produk_id', '=', 'up.id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->whereIn('up.usaha_id', $usahaIds);

        $query = $this->applyPengerajinFilter($query, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id'))
            $query->where('u.id', $request->usaha_id);
        if ($start)
            $query->whereDate('o.created_at', '>=', $start);
        if ($end)
            $query->whereDate('o.created_at', '<=', $end);

        return $query;
    }

    // =========================================================================
    // PENDAPATAN PER USAHA
    // =========================================================================
    protected function basePendapatanUsahaQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->whereIn('up.usaha_id', $usahaIds);

        $query = $this->applyPengerajinFilter($query, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id'))
            $query->where('u.id', $request->usaha_id);
        if ($request->filled('kategori_id'))
            $query->where('k.id', $request->kategori_id);
        if ($start)
            $query->whereDate('o.created_at', '>=', $start);
        if ($end)
            $query->whereDate('o.created_at', '<=', $end);

        return $query;
    }

    // =========================================================================
    // PRODUK FAVORITE
    // =========================================================================
    protected function baseProdukFavoriteQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('produk as p')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->leftJoin('produk_likes as pl', 'pl.produk_id', '=', 'p.id')
            ->whereIn('up.usaha_id', $usahaIds);

        $query = $this->applyPengerajinFilter($query, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id'))
            $query->where('u.id', $request->usaha_id);
        if ($request->filled('user_id'))
            $query->where('pl.user_id', $request->user_id);
        if ($start)
            $query->whereDate('pl.created_at', '>=', $start);
        if ($end)
            $query->whereDate('pl.created_at', '<=', $end);

        return $query;
    }

    // =========================================================================
    // PRODUK SLOW MOVING
    // =========================================================================
    protected function baseProdukSlowMovingQueryPengerajin(
        Request $request,
        ?string $start,
        ?string $end,
        int $threshold
    ): Builder {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('produk as p')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->join('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->leftJoin('order_items as oi', function ($join) use ($start, $end) {
                $join->on('oi.usaha_produk_id', '=', 'up.id');
                if ($start)
                    $join->whereDate('oi.created_at', '>=', $start);
                if ($end)
                    $join->whereDate('oi.created_at', '<=', $end);
            })
            ->whereIn('up.usaha_id', $usahaIds);

        $query = $this->applyPengerajinFilter($query, $filterPengerajinId, 'up', 'p');

        if ($request->filled('usaha_id'))
            $query->where('u.id', $request->usaha_id);
        if ($request->filled('kategori_id'))
            $query->where('k.id', $request->kategori_id);

        $query->groupBy('p.id', 'p.nama_produk', 'u.id', 'u.nama_usaha')
            ->selectRaw('
                u.nama_usaha,
                p.nama_produk,
                COALESCE(SUM(oi.quantity), 0) as total_terjual,
                MAX(oi.created_at) as transaksi_terakhir
            ')
            ->havingRaw('COALESCE(SUM(oi.quantity), 0) < ?', [$threshold])
            ->orderBy('total_terjual', 'asc')
            ->orderBy('p.nama_produk');

        return $query;
    }

    // =========================================================================
    // PRODUK TERLARIS
    // =========================================================================
    protected function baseProdukTerlarisQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->whereIn('up.usaha_id', $usahaIds);

        $query = $this->applyPengerajinFilter($query, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id'))
            $query->where('us.id', $request->usaha_id);
        if ($request->filled('user_id'))
            $query->where('o.user_id', $request->user_id);
        if ($request->filled('kategori_id'))
            $query->where('k.id', $request->kategori_id);
        if ($start)
            $query->whereDate('o.created_at', '>=', $start);
        if ($end)
            $query->whereDate('o.created_at', '<=', $end);

        return $query;
    }

    // =========================================================================
    // PRODUK VIEWS
    // =========================================================================
    protected function baseProdukViewsQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $viewsQuery = DB::table('produk as p')
            ->join('produk_views as pv', 'pv.produk_id', '=', 'p.id')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->whereIn('up.usaha_id', $usahaIds);

        $viewsQuery = $this->applyPengerajinFilter($viewsQuery, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id'))
            $viewsQuery->where('u.id', $request->usaha_id);
        if ($request->filled('user_id'))
            $viewsQuery->where('pv.user_id', $request->user_id);
        if ($start)
            $viewsQuery->whereDate('pv.created_at', '>=', $start);
        if ($end)
            $viewsQuery->whereDate('pv.created_at', '<=', $end);

        return $viewsQuery;
    }

    // =========================================================================
    // TRANSAKSI PER USER (PEMBELI)
    // =========================================================================
    protected function baseTransaksiUserQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->whereIn('up.usaha_id', $usahaIds);

        $query = $this->applyPengerajinFilter($query, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id'))
            $query->where('us.id', $request->usaha_id);
        if ($request->filled('user_id'))
            $query->where('o.user_id', $request->user_id);
        if ($request->filled('kategori_id'))
            $query->where('k.id', $request->kategori_id);
        if ($start)
            $query->whereDate('o.created_at', '>=', $start);
        if ($end)
            $query->whereDate('o.created_at', '<=', $end);

        return $query;
    }

    // =========================================================================
    // SEMUA TRANSAKSI
    // =========================================================================
    protected function baseTransaksiQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $base = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->whereIn('up.usaha_id', $usahaIds);

        $base = $this->applyPengerajinFilter($base, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('status'))
            $base->where('o.status', $request->status);
        if ($request->filled('usaha_id'))
            $base->where('us.id', $request->usaha_id);
        if ($request->filled('user_id'))
            $base->where('o.user_id', $request->user_id);
        if ($request->filled('kategori_id'))
            $base->where('k.id', $request->kategori_id);

        if ($start)
            $base->whereDate('o.created_at', '>=', $start);
        if ($end)
            $base->whereDate('o.created_at', '<=', $end);

        return $base;
    }
}
