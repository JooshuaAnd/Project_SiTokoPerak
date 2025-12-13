<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usaha;
use App\Models\User;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengerajinExport;
use App\Exports\SimpleCollectionExport;


class LaporanUsahaController extends Controller
{
    /**
     * Helper untuk nentuin range tanggal dari:
     * - periode_type (day/week/month/year/custom)
     * - start_date & end_date (filter lama)
     * - defaultLastDays (misal 30 hari terakhir untuk slow moving)
     */
    protected function resolveDateRange(Request $request, ?int $defaultLastDays = null): array
    {
        $periodeType = $request->input('periode_type'); // day, week, month, year atau null
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
                // input type="week" format: YYYY-Www, contoh: 2025-W09
                if ($request->filled('periode_week')) {
                    [$year, $week] = explode('-W', $request->input('periode_week'));
                    $startCarbon = Carbon::now()->setISODate((int) $year, (int) $week)->startOfWeek();
                    $endCarbon = Carbon::now()->setISODate((int) $year, (int) $week)->endOfWeek();
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

            // default: custom pakai start_date & end_date
        }

        // fallback kalau semua kosong & ada default (misal 30 hari terakhir)
        if (!$startCarbon && !$endCarbon && $defaultLastDays) {
            $endCarbon = Carbon::now()->endOfDay();
            $startCarbon = $endCarbon->copy()->subDays($defaultLastDays - 1)->startOfDay();
        }

        return [$startCarbon, $endCarbon];
    }

    /* =========================================================================
     * DASHBOARD UTAMA
     * ====================================================================== */

    /**
     * Dashboard laporan utama
     * URL: /admin/laporan-usaha
     * Route name: admin.laporan.index
     */
    public function index(Request $request)
    {
        // ---------- 1. DATA FILTER (TAHUN / BULAN / USAHA / KATEGORI / USER) ----------
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
            12 => 'Desember',
        ];

        $usahaList = Usaha::all();
        $kategoriList = KategoriProduk::all();
        // Mengambil daftar User yang berperan sebagai Pengerajin untuk filter
        $pengerajinList = User::where('role', 'pengerajin')->get();

        // ---------- 2. BASE QUERY UTAMA (REVISI RANTAI PENGERAJIN) ----------
        // Rantai: orders -> order_items -> usaha_produk -> produk -> pengerajin -> users (Pengerajin)
        $base = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('usaha_produk', 'order_items.usaha_produk_id', '=', 'usaha_produk.id')
            ->join('produk', 'usaha_produk.produk_id', '=', 'produk.id')
            ->leftJoin('kategori_produk', 'produk.kategori_produk_id', '=', 'kategori_produk.id')

            // STEP 4a: produk -> pengerajin (tabel fisik)
            ->leftJoin('pengerajin', 'produk.pengerajin_id', '=', 'pengerajin.id')

            // STEP 4b: pengerajin -> users (Akun Pengerajin, diasumsikan pengerajin.user_id)
            ->leftJoin('users as pengerajin_users', 'pengerajin.user_id', '=', 'pengerajin_users.id')

            // STEP 5: orders -> pembeli (users)
            ->leftJoin('users', 'orders.user_id', '=', 'users.id');

        // Wajib: Hanya hitung produk yang terikat ke pengerajin (filter global)
        $base->whereNotNull('produk.pengerajin_id');

        // ---------- 3. APLIKASI FILTER ----------
        if ($request->filled('tahun')) {
            $base->whereYear('orders.created_at', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $base->whereMonth('orders.created_at', $request->bulan);
        }

        if ($request->filled('kategori_id')) {
            $base->where('kategori_produk.id', $request->kategori_id);
        }

        // FILTER BERDASARKAN PENGERAJIN USER ID (pengerajin_users.id)
        if ($request->filled('user_id')) {
            $base->where('pengerajin_users.id', $request->user_id);
        }

        // FILTER USAHA: HANYA PERLU JOIN KE 'USAHA' dan FILTER
        $joinUsahaNeeded = false;
        if ($request->filled('usaha_id')) {
            $base->join('usaha', 'usaha_produk.usaha_id', '=', 'usaha.id')
                ->where('usaha.id', $request->usaha_id);
            $joinUsahaNeeded = true;
        }

        // Kloning base query setelah semua filter diaplikasikan
        $baseQuery = clone $base;

        // ---------- 4. METRIC ATAS ----------
        $totalTransaksi = (clone $baseQuery)
            ->distinct('orders.id')
            ->count('orders.id');

        $totalPendapatan = (clone $baseQuery)
            ->selectRaw('SUM(order_items.quantity * order_items.price_at_purchase) as total')
            ->value('total') ?? 0;

        // ---------- 5. PENDAPATAN PER USAHA (GROUPING) ----------
        $baseUsahaGroup = clone $base;

        if (!$joinUsahaNeeded) {
            $baseUsahaGroup->leftJoin('usaha', 'usaha_produk.usaha_id', '=', 'usaha.id');
        }

        $pendapatanPerUsaha = (clone $baseUsahaGroup)
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

        // ---------- 5b. PENDAPATAN PER PENGERAJIN (GROUPING) ----------
        $pendapatanPerPengerajin = (clone $baseQuery)
            // Sekarang menggunakan pengerajin_users.username
            ->selectRaw('pengerajin_users.username, SUM(order_items.quantity * order_items.price_at_purchase) as total')
            ->whereNotNull('pengerajin_users.id')
            ->groupBy('pengerajin_users.id', 'pengerajin_users.username')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $pendapatanPengerajinChart = [
            'labels' => $pendapatanPerPengerajin->pluck('username'),
            'data' => $pendapatanPerPengerajin->pluck('total'),
        ];

        // ---------- 6. TOP PRODUK ----------
        // ... (Logika tetap sama, menggunakan baseQuery)

        $topProdukRow = (clone $baseQuery)
            ->selectRaw('produk.nama_produk, SUM(order_items.quantity) as total_qty')
            ->groupBy('produk.id', 'produk.nama_produk')
            ->orderByDesc('total_qty')
            ->first();

        $topProduk = $topProdukRow->nama_produk ?? null;

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

        // ---------- 7. TOP USER (Pembeli Aktif) ----------
        // ... (Logika Pembeli tetap sama)

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

        // ---------- 8. TOP KATEGORI ----------
        // ... (Logika tetap sama)

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

        // ---------- 9. PRODUK FAVORITE & VIEWS (Logika tetap sama) ----------
        $produkFavorite = DB::table('produk_likes as pl')
            ->join('produk as p', 'p.id', '=', 'pl.produk_id')
            ->selectRaw('p.nama_produk, COUNT(pl.id) as total_like')
            ->groupBy('p.id', 'p.nama_produk')
            ->orderByDesc('total_like')
            ->limit(3)
            ->get();

        $produkFavoriteChart = [
            'labels' => $produkFavorite->pluck('nama_produk'),
            'data' => $produkFavorite->pluck('total_like'),
        ];

        $produkViews = DB::table('produk_views as pv')
            ->join('produk as p', 'p.id', '=', 'pv.produk_id')
            ->selectRaw('p.nama_produk, COUNT(pv.id) as total_view')
            ->groupBy('p.id', 'p.nama_produk')
            ->orderByDesc('total_view')
            ->limit(3)
            ->get();

        $produkViewChart = [
            'labels' => $produkViews->pluck('nama_produk'),
            'data' => $produkViews->pluck('total_view'),
        ];

        // ---------- 10. RETURN ----------
        return view('admin.laporan_usaha.laporan', compact(
            'tahunList',
            'bulanList',
            'usahaList',
            'kategoriList',
            'pengerajinList',
            'totalTransaksi',
            'totalPendapatan',
            'pendapatanChart',
            'pendapatanPengerajinChart',
            'produkTerlarisChart',
            'produkFavoriteChart',
            'produkViewChart',
            'transaksiUserChart',
            'kategoriChart',
            'topProduk',
            'userAktif',
        ));
    }

    /* =========================================================================
     * KATEGORI PRODUK
     * ====================================================================== */

    // File: app/Http/Controllers/Admin/LaporanUsahaController.php

    protected function baseKategoriProdukQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('kategori_produk as k')
            ->leftJoin('produk as p', 'p.kategori_produk_id', '=', 'k.id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('order_items as oi', 'oi.usaha_produk_id', '=', 'up.id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')

            // TAMBAHAN: Join Pengerajin untuk filter user_id
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pu', 'pengerajin.user_id', '=', 'pu.id'); // Alias pu = Pengerajin User

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }

        // TAMBAHAN: Filter Pengerajin
        if ($request->filled('user_id')) {
            $query->where('pu.id', $request->user_id);
        }

        return $query;
    }

    /**
     * Laporan Kategori Produk
     */
    /* =========================================================================
     * PENDAPATAN PER USAHA
     * ====================================================================== */

    protected function basePendapatanUsahaQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id') // Corrected join from up.produk_id
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')

            // TAMBAHAN: Join ke Pengerajin untuk filter user_id
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pu', 'pengerajin.user_id', '=', 'pu.id'); // Alias pu = Pengerajin User

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('k.id', $request->kategori_id);
        }
        // TAMBAHAN: Filter berdasarkan Pengerajin User
        if ($request->filled('user_id')) {
            $query->where('pu.id', $request->user_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }

        return $query;
    }
    /**
     * Laporan Pendapatan Per Usaha
     */


    /* =========================================================================
     * PRODUK FAVORITE (LIKE)
     * ====================================================================== */

    protected function baseProdukFavoriteQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('produk as p')
            ->leftJoin('produk_likes as pl', 'pl.produk_id', '=', 'p.id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id');

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($start) {
            $query->whereDate('pl.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('pl.created_at', '<=', $end);
        }

        return $query;
    }

    /**
     * Laporan Produk Favorite (Like)
     */
    /* =========================================================================
     * PRODUK SLOW MOVING
     * ====================================================================== */

    protected function baseProdukSlowMovingQuery(Request $request, ?string $start, ?string $end, int $threshold)
    {
        $query = DB::table('produk as p')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->leftJoin('order_items as oi', function ($join) use ($start, $end) {
                $join->on('oi.usaha_produk_id', '=', 'up.id');
                if ($start) {
                    $join->whereDate('oi.created_at', '>=', $start);
                }
                if ($end) {
                    $join->whereDate('oi.created_at', '<=', $end);
                }
            });

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('k.id', $request->kategori_id);
        }

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

    /**
     * Laporan Produk Slow Moving
     */

    /* =========================================================================
     * PRODUK TERLARIS
     * ====================================================================== */

    protected function baseProdukTerlarisQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')

            // REVISI KRITIS: Join ke Produk harus dari usaha_produk
            ->join('produk as p', 'p.id', '=', 'up.produk_id');

        // TAMBAHAN: Join Pengerajin untuk filter user_id
        $query->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pu', 'pengerajin.user_id', '=', 'pu.id');

        if ($request->filled('usaha_id')) {
            $query->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('p.kategori_produk_id', $request->kategori_id);
        }
        // TAMBAHAN: Filter Pengerajin
        if ($request->filled('user_id')) {
            $query->where('pu.id', $request->user_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }

        return $query;
    }
    /**
     * Laporan Produk Terlaris
     */

    /* =========================================================================
     * PRODUK VIEWS
     * ====================================================================== */

    protected function baseProdukViewsQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $viewsQuery = DB::table('produk as p')
            ->join('produk_views as pv', 'pv.produk_id', '=', 'p.id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id');

        if ($request->filled('usaha_id')) {
            $viewsQuery->where('u.id', $request->usaha_id);
        }
        if ($start) {
            $viewsQuery->whereDate('pv.created_at', '>=', $start);
        }
        if ($end) {
            $viewsQuery->whereDate('pv.created_at', '<=', $end);
        }

        return $viewsQuery;
    }

    /**
     * Laporan Views Produk
     */

    /* =========================================================================
     * TRANSAKSI PER USER
     * ====================================================================== */

    protected function baseTransaksiUserQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'oi.produk_id');

        if ($request->filled('usaha_id')) {
            $query->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('p.kategori_produk_id', $request->kategori_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }

        return $query;
    }

    /**
     * Laporan Transaksi Per User
     */

    /* =========================================================================
     * SEMUA TRANSAKSI
     * ====================================================================== */

    protected function baseTransaksiQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $base = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->leftJoin('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->leftJoin('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->leftJoin('produk as p', 'p.id', '=', 'oi.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id');

        if ($request->filled('usaha_id')) {
            $base->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $base->where('k.id', $request->kategori_id);
        }
        if ($request->filled('user_id')) {
            $base->where('u.id', $request->user_id);
        }
        if ($request->filled('status')) {
            $base->where('o.status', $request->status);
        }
        if ($start) {
            $base->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $base->whereDate('o.created_at', '<=', $end);
        }

        return $base;
    }

    /**
     * Laporan Semua Transaksi
     */


    /* =========================================================================
     * EXPORT PENGERAJIN (DARI CONTROLLER LAMA)
     * ====================================================================== */

    //belum dipakai

    public function exportPengerajin()
    {
        return Excel::download(new PengerajinExport, 'pengerajin.xlsx');
    }
}

