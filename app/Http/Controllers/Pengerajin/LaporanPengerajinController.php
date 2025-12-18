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
        if (!Auth::check() || !$userId) abort(401, 'Silakan login.');

        $pengerajin = Pengerajin::where('user_id', $userId)->first();
        if (!$pengerajin) abort(403, 'Akses ditolak. Akun Anda tidak terdaftar sebagai Pengerajin aktif.');

        $pengerajinId = (int) $pengerajin->id;

        $usahaIds = DB::table('usaha_pengerajin')
            ->where('pengerajin_id', $pengerajinId)
            ->pluck('usaha_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        if (empty($usahaIds)) abort(403, 'Akses ditolak. Pengerajin ini belum dipetakan ke usaha manapun.');

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

    protected function resolveFilterPengerajinId(Request $request, int $loginPengerajinId, array $usahaIds): ?int
    {
        if ($request->filled('pengerajin_id')) {
            $id = (int) $request->pengerajin_id;

            $allowed = DB::table('usaha_pengerajin')
                ->whereIn('usaha_id', $usahaIds)
                ->where('pengerajin_id', $id)
                ->exists();

            if (!$allowed) abort(403, 'Pengerajin tidak valid untuk usaha ini.');
            return $id;
        }

        if ($request->has('apply_filters')) {
            return null; // SEMUA
        }

        $request->merge(['pengerajin_id' => $loginPengerajinId]);
        return $loginPengerajinId;
    }

    protected function applyPengerajinFilter(Builder $q, ?int $filterPengerajinId, string $pAlias = 'p'): Builder
    {
        if (!$filterPengerajinId) return $q;
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
                        $tmp = Carbon::now()->setISODate((int)$year, (int)$week);
                        $startCarbon = $tmp->copy()->startOfDay();
                        $endCarbon = $tmp->copy()->endOfWeek();
                    } catch (\Throwable $e) {}
                }
                break;

            case 'month':
                if ($request->filled('periode_year') && $request->filled('periode_month')) {
                    $startCarbon = Carbon::createFromDate(
                        (int)$request->input('periode_year'),
                        (int)$request->input('periode_month'),
                        1
                    )->startOfDay();
                    $endCarbon = $startCarbon->copy()->endOfMonth();
                }
                break;

            case 'year':
                if ($request->filled('periode_year')) {
                    $year = (int)$request->input('periode_year');
                    $startCarbon = Carbon::createFromDate($year, 1, 1)->startOfDay();
                    $endCarbon = Carbon::createFromDate($year, 12, 31)->endOfDay();
                }
                break;
        }

        if (!$startCarbon && !$endCarbon && $defaultLastDays) {
            $endCarbon = Carbon::now()->endOfDay();
            $startCarbon = $endCarbon->copy()->subDays($defaultLastDays - 1)->startOfDay();
        }

        return [$startCarbon, $endCarbon];
    }

    // =========================================================================
    // BASE QUERY UTAMA (FIX: no usaha_produk, no oi.usaha_produk_id)
    // =========================================================================
    protected function baseQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $hasSimplePeriode = $request->filled('tahun') || $request->filled('bulan');
        [$start, $end] = $this->resolveDateRange($request, $hasSimplePeriode ? null : 30);

        $base = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('produk as p', 'p.id', '=', 'oi.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->join('usaha_pengerajin as map', 'map.pengerajin_id', '=', 'p.pengerajin_id')
            ->join('usaha as us', 'us.id', '=', 'map.usaha_id')
            ->whereIn('map.usaha_id', $usahaIds);

        $base = $this->applyPengerajinFilter($base, $filterPengerajinId, 'p');

        if ($start) $base->whereDate('o.created_at', '>=', $start);
        if ($end) $base->whereDate('o.created_at', '<=', $end);

        if ($request->filled('tahun')) $base->whereYear('o.created_at', $request->tahun);
        if ($request->filled('bulan')) $base->whereMonth('o.created_at', $request->bulan);

        if ($request->filled('kategori_id')) $base->where('k.id', $request->kategori_id);
        if ($request->filled('usaha_id')) $base->where('us.id', $request->usaha_id);

        return $base;
    }

    // =========================================================================
    // DASHBOARD UTAMA (index) - cukup pakai baseQueryPengerajin yang sudah fixed
    // =========================================================================
    public function index(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $currentYear = now()->year;
        $tahunList = range($currentYear - 5, $currentYear);
        rsort($tahunList);

        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();

        // role di schema kamu: admin/guest/pengerajin (bukan customer)
        $userList = User::where('role', 'guest')->get();

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

        $base = $this->baseQueryPengerajin($request);
        $baseQuery = clone $base;

        $totalTransaksi = (clone $baseQuery)->distinct('o.id')->count('o.id');

        $totalPendapatan = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(oi.quantity * oi.price_at_purchase),0) as total')
            ->value('total') ?? 0;

        $pendapatanPerUsaha = (clone $baseQuery)
            ->selectRaw('us.nama_usaha, SUM(oi.quantity * oi.price_at_purchase) as total')
            ->groupBy('us.id', 'us.nama_usaha')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $pendapatanChart = [
            'labels' => $pendapatanPerUsaha->pluck('nama_usaha'),
            'data' => $pendapatanPerUsaha->pluck('total'),
        ];

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

        // LIKE & VIEW (schema kamu pakai session_id, bukan user_id)
        $produkFavoriteQ = DB::table('produk as p')
            ->join('usaha_pengerajin as map', 'map.pengerajin_id', '=', 'p.pengerajin_id')
            ->join('usaha as us', 'us.id', '=', 'map.usaha_id')
            ->leftJoin('produk_likes as pl', 'pl.produk_id', '=', 'p.id')
            ->whereIn('map.usaha_id', $usahaIds);

        $produkFavoriteQ = $this->applyPengerajinFilter($produkFavoriteQ, $filterPengerajinId, 'p');
        if ($request->filled('usaha_id')) $produkFavoriteQ->where('us.id', $request->usaha_id);

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

        $produkViewsQ = DB::table('produk as p')
            ->join('usaha_pengerajin as map', 'map.pengerajin_id', '=', 'p.pengerajin_id')
            ->join('usaha as us', 'us.id', '=', 'map.usaha_id')
            ->leftJoin('produk_views as pv', 'pv.produk_id', '=', 'p.id')
            ->whereIn('map.usaha_id', $usahaIds);

        $produkViewsQ = $this->applyPengerajinFilter($produkViewsQ, $filterPengerajinId, 'p');
        if ($request->filled('usaha_id')) $produkViewsQ->where('us.id', $request->usaha_id);

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

    // Kalau kamu punya page laporan lain (pendapatan usaha, kategori, transaksi, dll)
    // pastikan base query-nya juga mengikuti pola o->oi->p->map->us seperti di atas.
}
