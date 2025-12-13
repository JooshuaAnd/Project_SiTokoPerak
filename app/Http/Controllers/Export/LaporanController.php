<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Usaha;
use App\Models\User;
use App\Models\Produk;
use App\Models\UsahaProduk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pengerajin;
use App\Models\UsahaPengerajin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SimpleCollectionExport;



class LaporanController extends Controller
{
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

    protected function baseTransaksiQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $base = DB::table('orders as o')
            // STEP 1: Pembeli (User)
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'o.id')

            // STEP 2: order_items -> usaha_produk
            ->leftJoin('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')

            // STEP 3: usaha_produk -> usaha
            ->leftJoin('usaha as us', 'us.id', '=', 'up.usaha_id')

            // STEP 4: usaha_produk -> produk (REVISI JOIN)
            ->leftJoin('produk as p', 'p.id', '=', 'up.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')

            // STEP 5: produk -> pengerajin -> users (UNTUK FILTER PENGERAJIN)
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pengerajin_users', 'pengerajin.user_id', '=', 'pengerajin_users.id');


        if ($request->filled('usaha_id')) {
            $base->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $base->where('k.id', $request->kategori_id);
        }

        // REVISI FILTER: user_id difilter pada Pengerajin (pengerajin_users)
        // Jika filter diisi, diasumsikan user ingin memfilter produk dari Pengerajin tertentu.
        if ($request->filled('user_id')) {
            $base->where('pengerajin_users.id', $request->user_id);
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

        // Tambahkan whereNotNull agar transaksi yang tidak punya Pengerajin tidak dihitung
        $base->whereNotNull('p.pengerajin_id');

        return $base;
    }

    public function transaksi(Request $request)
    {
        $usahaList = Usaha::all();
        $kategoriList = KategoriProduk::all();

        // PENTING: userList ini adalah daftar Pengerajin
        $userList = User::where('role', 'pengerajin')->get();

        $statusList = ['baru', 'dibayar', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

        [$start, $end] = $this->resolveDateRange($request, null);

        $transaksi = $this->baseTransaksiQuery($request, $start, $end)
            // GROUP BY HARUS MENGGUNAKAN o.id KARENA MENGELOMPOKKAN PER TRANSAKSI
            ->groupBy('o.id', 'u.username', 'o.customer_name', 'o.total_amount', 'o.status', 'o.created_at')
            ->selectRaw('
                o.id,
                COALESCE(u.username, o.customer_name) as username,
                o.total_amount as total,
                DATE_FORMAT(o.created_at, "%d-%m-%Y %H:%i") as tanggal_transaksi,
                o.status
            ')
            ->orderByDesc('o.created_at')
            ->get();

        $totalTransaksi = $transaksi->count();
        $totalNominal = (int) $transaksi->sum('total');

        return view('admin.laporan_usaha.transaksi', compact(
            'usahaList',
            'kategoriList',
            'userList',
            'statusList',
            'transaksi',
            'totalTransaksi',
            'totalNominal',
        ));
    }

    public function exportTransaksi(Request $request)
    {
        // ... (Logika export sama, menggunakan baseTransaksiQuery yang sudah direvisi)

        $statusList = ['baru', 'dibayar', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

        [$start, $end] = $this->resolveDateRange($request, null);

        $rows = $this->baseTransaksiQuery($request, $start, $end)
            ->groupBy('o.id', 'u.username', 'o.customer_name', 'o.total_amount', 'o.status', 'o.created_at')
            ->selectRaw('
                o.id,
                COALESCE(u.username, o.customer_name) as username,
                o.total_amount as total,
                DATE_FORMAT(o.created_at, "%d-%m-%Y %H:%i") as tanggal_transaksi,
                o.status
            ')
            ->orderByDesc('o.created_at')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(function ($row) {
                return [
                    $row->id,
                    $row->username,
                    $row->total,
                    $row->tanggal_transaksi,
                    $row->status,
                ];
            }),
            ['ID', 'User', 'Total (Rp)', 'Tanggal', 'Status']
        );

        $filename = 'transaksi_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }
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
    public function kategoriProduk(Request $request)
    {
        $usahaList = Usaha::all();

        [$start, $end] = $this->resolveDateRange($request, null);

        $laporan = $this->baseKategoriProdukQuery($request, $start, $end)
            ->groupBy('k.id', 'k.nama_kategori_produk')
            ->selectRaw('
                k.nama_kategori_produk,
                COUNT(DISTINCT p.id) as total_produk,
                COALESCE(SUM(oi.quantity), 0) as total_terjual
            ')
            ->orderBy('k.nama_kategori_produk')
            ->get();

        return view('admin.laporan_usaha.kategori_produk', compact('usahaList', 'laporan'));
    }

    public function exportKategoriProduk(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request, null);

        $rows = $this->baseKategoriProdukQuery($request, $start, $end)
            ->groupBy('k.id', 'k.nama_kategori_produk')
            ->selectRaw('
                k.nama_kategori_produk,
                COUNT(DISTINCT p.id) as total_produk,
                COALESCE(SUM(oi.quantity), 0) as total_terjual
            ')
            ->orderBy('k.nama_kategori_produk')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(function ($row) {
                return [
                    $row->nama_kategori_produk,
                    $row->total_produk,
                    $row->total_terjual,
                ];
            }),
            ['Kategori', 'Total Produk', 'Total Terjual']
        );

        $filename = 'kategori_produk_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }
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
    public function pendapatanUsaha(Request $request)
    {
        $usahaList = Usaha::all();
        $kategoriList = KategoriProduk::all();

        [$start, $end] = $this->resolveDateRange($request, null);

        $laporan = $this->basePendapatanUsahaQuery($request, $start, $end)
            ->groupBy('u.id', 'u.nama_usaha')
            ->selectRaw('
                u.nama_usaha,
                COUNT(DISTINCT o.id) as total_transaksi,
                SUM(oi.quantity * oi.price_at_purchase) as total_penjualan,
                SUM(oi.quantity * oi.price_at_purchase) / NULLIF(COUNT(DISTINCT o.id), 0) as rata_rata_transaksi,
                MAX(o.created_at) as transaksi_terakhir
            ')
            ->orderByDesc('total_penjualan')
            ->get();

        $totalUsaha = $laporan->count();
        $totalTransaksi = (int) $laporan->sum('total_transaksi');
        $totalPendapatan = (int) $laporan->sum('total_penjualan');
        $avgTransaksiGlobal = $totalTransaksi > 0
            ? (int) floor($totalPendapatan / $totalTransaksi)
            : 0;

        return view('admin.laporan_usaha.pendapatan_usaha', compact(
            'usahaList',
            'kategoriList',
            'laporan',
            'totalUsaha',
            'totalTransaksi',
            'totalPendapatan',
            'avgTransaksiGlobal',
        ));
    }

    public function exportPendapatanUsaha(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request, null);

        $rows = $this->basePendapatanUsahaQuery($request, $start, $end)
            ->groupBy('u.id', 'u.nama_usaha')
            ->selectRaw('
                u.nama_usaha,
                COUNT(DISTINCT o.id) as total_transaksi,
                SUM(oi.quantity * oi.price_at_purchase) as total_penjualan,
                SUM(oi.quantity * oi.price_at_purchase) / NULLIF(COUNT(DISTINCT o.id), 0) as rata_rata_transaksi,
                MAX(o.created_at) as transaksi_terakhir
            ')
            ->orderByDesc('total_penjualan')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(function ($row) {
                return [
                    $row->nama_usaha,
                    $row->total_transaksi,
                    $row->total_penjualan,
                    $row->rata_rata_transaksi,
                    $row->transaksi_terakhir,
                ];
            }),
            ['Usaha', 'Total Transaksi', 'Total Penjualan', 'Rata-rata Transaksi', 'Transaksi Terakhir']
        );

        $filename = 'pendapatan_usaha_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

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
    public function produkFavorite(Request $request)
    {
        $usahaList = Usaha::all();

        [$start, $end] = $this->resolveDateRange($request, null);

        $laporan = $this->baseProdukFavoriteQuery($request, $start, $end)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(pl.id) as total_like')
            ->orderByDesc('total_like')
            ->get();

        $totalProduk = Produk::count();

        return view('admin.laporan_usaha.produk_favorite', compact(
            'usahaList',
            'laporan',
            'totalProduk',
        ));
    }

    public function exportProdukFavorite(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request, null);

        $rows = $this->baseProdukFavoriteQuery($request, $start, $end)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(pl.id) as total_like')
            ->orderByDesc('total_like')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(function ($row) {
                return [
                    $row->nama_produk,
                    $row->total_like,
                ];
            }),
            ['Nama Produk', 'Total Like']
        );

        $filename = 'produk_favorite_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

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
    public function produkSlowMoving(Request $request)
    {
        $usahaList = Usaha::all();
        $kategoriList = KategoriProduk::all();
        $threshold = 5;

        // default 30 hari terakhir jika tidak ada input
        [$startCarbon, $endCarbon] = $this->resolveDateRange($request, 30);
        $start = $startCarbon ? $startCarbon->toDateString() : null;
        $end = $endCarbon ? $endCarbon->toDateString() : null;

        $laporan = $this->baseProdukSlowMovingQuery($request, $start, $end, $threshold)->get();

        $totalProdukSlow = $laporan->count();
        $totalQtyTerjual = (int) $laporan->sum('total_terjual');

        return view('admin.laporan_usaha.produk_slow_moving', compact(
            'usahaList',
            'kategoriList',
            'laporan',
            'start',
            'end',
            'threshold',
            'totalProdukSlow',
            'totalQtyTerjual',
        ));
    }

    public function exportProdukSlowMoving(Request $request)
    {
        $threshold = 5;
        [$startCarbon, $endCarbon] = $this->resolveDateRange($request, 30);
        $start = $startCarbon ? $startCarbon->toDateString() : null;
        $end = $endCarbon ? $endCarbon->toDateString() : null;

        $rows = $this->baseProdukSlowMovingQuery($request, $start, $end, $threshold)->get();

        $export = new SimpleCollectionExport(
            $rows->map(function ($row) {
                return [
                    $row->nama_usaha,
                    $row->nama_produk,
                    $row->total_terjual,
                    $row->transaksi_terakhir,
                ];
            }),
            ['Usaha', 'Nama Produk', 'Total Terjual', 'Transaksi Terakhir']
        );

        $filename = 'produk_slow_moving_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

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
    public function produkTerlaris(Request $request)
    {
        $usahaList = Usaha::all();
        $kategoriList = KategoriProduk::all();

        [$start, $end] = $this->resolveDateRange($request, null);

        $laporan = $this->baseProdukTerlarisQuery($request, $start, $end)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, SUM(oi.quantity) as total_terjual')
            ->orderByDesc('total_terjual')
            ->get();

        $totalProduk = $laporan->count();
        $totalTerjual = (int) $laporan->sum('total_terjual');
        $topRow = $laporan->first();
        $chartData = $laporan->take(10)->values();

        return view('admin.laporan_usaha.produk_terlaris', compact(
            'usahaList',
            'kategoriList',
            'laporan',
            'totalProduk',
            'totalTerjual',
            'topRow',
            'chartData',
        ));
    }

    public function exportProdukTerlaris(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request, null);

        $rows = $this->baseProdukTerlarisQuery($request, $start, $end)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, SUM(oi.quantity) as total_terjual')
            ->orderByDesc('total_terjual')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(function ($row) {
                return [
                    $row->nama_produk,
                    $row->total_terjual,
                ];
            }),
            ['Nama Produk', 'Total Terjual']
        );

        $filename = 'produk_terlaris_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

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
    public function produkViews(Request $request)
    {
        $usahaList = Usaha::all();

        [$start, $end] = $this->resolveDateRange($request, null);

        // Total produk (optionally filter by usaha)
        $produkBase = DB::table('produk as p')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id');

        if ($request->filled('usaha_id')) {
            $produkBase->where('u.id', $request->usaha_id);
        }

        $totalProduk = $produkBase->distinct('p.id')->count('p.id');

        // Views per produk
        $produkViews = $this->baseProdukViewsQuery($request, $start, $end)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(pv.id) as total_views')
            ->orderByDesc('total_views')
            ->get();

        $produkDenganViews = $produkViews->count();
        $totalViews = (int) $produkViews->sum('total_views');

        return view('admin.laporan_usaha.produk_views', compact(
            'usahaList',
            'totalProduk',
            'produkDenganViews',
            'totalViews',
            'produkViews',
        ));
    }

    public function exportProdukViews(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request, null);

        $rows = $this->baseProdukViewsQuery($request, $start, $end)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(pv.id) as total_views')
            ->orderByDesc('total_views')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(function ($row) {
                return [
                    $row->nama_produk,
                    $row->total_views,
                ];
            }),
            ['Nama Produk', 'Total Views']
        );

        $filename = 'produk_views_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

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
    public function transaksiUser(Request $request)
    {
        $usahaList = Usaha::all();
        $kategoriList = KategoriProduk::all();

        [$start, $end] = $this->resolveDateRange($request, null);

        $laporan = $this->baseTransaksiUserQuery($request, $start, $end)
            ->groupBy('u.id', 'u.username')
            ->selectRaw('
                u.username,
                COUNT(DISTINCT o.id) as total_transaksi,
                SUM(oi.quantity * oi.price_at_purchase) as total_belanja
            ')
            ->orderByDesc('total_belanja')
            ->get();

        $totalUser = $laporan->count();
        $totalTransaksi = (int) $laporan->sum('total_transaksi');
        $totalBelanja = (int) $laporan->sum('total_belanja');

        return view('admin.laporan_usaha.transaksi_user', compact(
            'usahaList',
            'kategoriList',
            'laporan',
            'totalUser',
            'totalTransaksi',
            'totalBelanja',
        ));
    }

    public function exportTransaksiUser(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request, null);

        $rows = $this->baseTransaksiUserQuery($request, $start, $end)
            ->groupBy('u.id', 'u.username')
            ->selectRaw('
                u.username,
                COUNT(DISTINCT o.id) as total_transaksi,
                SUM(oi.quantity * oi.price_at_purchase) as total_belanja
            ')
            ->orderByDesc('total_belanja')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(function ($row) {
                return [
                    $row->username,
                    $row->total_transaksi,
                    $row->total_belanja,
                ];
            }),
            ['User', 'Total Transaksi', 'Total Belanja']
        );

        $filename = 'transaksi_per_user_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

}
