<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SimpleCollectionExport;
use Carbon\Carbon;
use App\Models\Usaha;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Pengerajin;
use App\Models\UsahaPengerajin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;


class PengerajinLaporanController extends Controller
{
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

        // mapping usaha hanya lewat pivot usaha_pengerajin
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
                // Input HTML type="week" -> format: YYYY-Www (misal: 2025-W09)
                if ($request->filled('periode_week')) {
                    $weekInput = $request->input('periode_week'); // contoh: "2025-W09"
                    try {
                        [$year, $week] = explode('-W', $weekInput);
                        $tmp = Carbon::now()->setISODate((int) $year, (int) $week);
                        $startCarbon = $tmp->copy()->startOfDay();
                        $endCarbon = $tmp->copy()->endOfWeek(); // akhir minggu (Minggu)
                    } catch (\Throwable $e) {
                        // kalau format aneh, biarkan saja -> fallback ke default / manual
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

        // Default: kalau user tidak pakai filter tanggal & dikasih defaultLastDays
        if (!$startCarbon && !$endCarbon && $defaultLastDays) {
            $endCarbon = Carbon::now()->endOfDay();
            $startCarbon = $endCarbon->copy()->subDays($defaultLastDays - 1)->startOfDay();
        }

        return [$startCarbon, $endCarbon];
    }

       // =========================================================================
    // KATEGORI PRODUK
    // =========================================================================
    protected function baseKategoriProdukQueryPengerajin(Request $request): Builder
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $query = DB::table('kategori_produk as k')
            ->leftJoin('produk as p', 'p.kategori_produk_id', '=', 'k.id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                $join->on('map.usaha_id', '=', 'up.usaha_id')
                    ->where('map.pengerajin_id', '=', $pengerajinId);
            })
            ->leftJoin('order_items as oi', 'oi.usaha_produk_id', '=', 'up.id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->whereIn('up.usaha_id', $usahaIds);

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }

        return $query;
    }

    public function kategoriProduk(Request $request)
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $pengerajinList = collect([Auth::user()]);

        $laporan = $this->baseKategoriProdukQueryPengerajin($request)
            ->groupBy('k.id', 'k.nama_kategori_produk')
            ->selectRaw('
                k.nama_kategori_produk,
                COUNT(DISTINCT p.id) as total_produk,
                COALESCE(SUM(oi.quantity), 0) as total_terjual
            ')
            ->orderBy('k.nama_kategori_produk')
            ->get();

        return view('pengerajin.laporan_usaha.kategori_produk', compact(
            'usahaList',
            'laporan',
            'pengerajinList'
        ));
    }

    public function exportKategoriProduk(Request $request)
    {
        $rows = $this->baseKategoriProdukQueryPengerajin($request)
            ->groupBy('k.id', 'k.nama_kategori_produk')
            ->selectRaw('
                k.nama_kategori_produk,
                COUNT(DISTINCT p.id) as total_produk,
                COALESCE(SUM(oi.quantity), 0) as total_terjual
            ')
            ->orderBy('k.nama_kategori_produk')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(fn($row) => [
                $row->nama_kategori_produk,
                $row->total_produk,
                $row->total_terjual,
            ]),
            ['Kategori', 'Total Produk', 'Total Terjual']
        );

        $filename = 'kategori_produk_pengerajin_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

    // =========================================================================
    // PENDAPATAN PER USAHA
    // =========================================================================
    protected function basePendapatanUsahaQueryPengerajin(Request $request): Builder
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $query = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                $join->on('map.usaha_id', '=', 'up.usaha_id')
                    ->where('map.pengerajin_id', '=', $pengerajinId);
            })
            ->whereIn('up.usaha_id', $usahaIds);

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('k.id', $request->kategori_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }

        return $query;
    }

    public function pendapatanUsaha(Request $request)
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $pengerajinList = collect([Auth::user()]);

        $laporan = $this->basePendapatanUsahaQueryPengerajin($request)
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

        return view('pengerajin.laporan_usaha.pendapatan_usaha', compact(
            'usahaList',
            'kategoriList',
            'laporan',
            'totalUsaha',
            'totalTransaksi',
            'totalPendapatan',
            'avgTransaksiGlobal',
            'pengerajinList'
        ));
    }

    public function exportPendapatanUsaha(Request $request)
    {
        $rows = $this->basePendapatanUsahaQueryPengerajin($request)
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
            $rows->map(fn($row) => [
                $row->nama_usaha,
                $row->total_transaksi,
                $row->total_penjualan,
                $row->rata_rata_transaksi,
                $row->transaksi_terakhir,
            ]),
            ['Usaha', 'Total Transaksi', 'Total Penjualan', 'Rata-rata Transaksi', 'Transaksi Terakhir']
        );

        $filename = 'pendapatan_usaha_pengerajin_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

    // =========================================================================
    // PRODUK FAVORITE
    // =========================================================================
    protected function baseProdukFavoriteQueryPengerajin(Request $request): Builder
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $query = DB::table('produk as p')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                $join->on('map.usaha_id', '=', 'up.usaha_id')
                    ->where('map.pengerajin_id', '=', $pengerajinId);
            })
            ->leftJoin('produk_likes as pl', 'pl.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->whereIn('up.usaha_id', $usahaIds);

        [$start, $end] = $this->resolveDateRange($request, null);

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

    public function produkFavorite(Request $request)
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $pengerajinList = collect([Auth::user()]);

        $laporan = $this->baseProdukFavoriteQueryPengerajin($request)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(pl.id) as total_like')
            ->orderByDesc('total_like')
            ->get();

        $totalProduk = DB::table('produk as p')
            ->whereExists(function ($q) use ($pengerajinId) {
                $q->select(DB::raw(1))
                    ->from('usaha_produk as up')
                    ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                        $join->on('map.usaha_id', '=', 'up.usaha_id')
                            ->where('map.pengerajin_id', '=', $pengerajinId);
                    })
                    ->whereColumn('up.produk_id', 'p.id');
            })
            ->distinct('p.id')
            ->count('p.id');

        return view('pengerajin.laporan_usaha.produk_favorite', compact(
            'usahaList',
            'laporan',
            'totalProduk',
            'pengerajinList'
        ));
    }

    public function exportProdukFavorite(Request $request)
    {
        $rows = $this->baseProdukFavoriteQueryPengerajin($request)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(pl.id) as total_like')
            ->orderByDesc('total_like')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(fn($row) => [
                $row->nama_produk,
                $row->total_like,
            ]),
            ['Nama Produk', 'Total Like']
        );

        $filename = 'produk_favorite_pengerajin_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
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
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $query = DB::table('produk as p')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->join('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                $join->on('map.usaha_id', '=', 'up.usaha_id')
                    ->where('map.pengerajin_id', '=', $pengerajinId);
            })
            ->leftJoin('order_items as oi', function ($join) use ($start, $end) {
                $join->on('oi.usaha_produk_id', '=', 'up.id');
                if ($start) {
                    $join->whereDate('oi.created_at', '>=', $start);
                }
                if ($end) {
                    $join->whereDate('oi.created_at', '<=', $end);
                }
            })
            ->whereIn('up.usaha_id', $usahaIds);

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

    public function produkSlowMoving(Request $request)
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $threshold = 5;

        [$startCarbon, $endCarbon] = $this->resolveDateRange($request, 30);
        $start = $startCarbon ? $startCarbon->toDateString() : null;
        $end = $endCarbon ? $endCarbon->toDateString() : null;
        $pengerajinList = collect([Auth::user()]);

        $laporan = $this->baseProdukSlowMovingQueryPengerajin($request, $start, $end, $threshold)->get();

        $totalProdukSlow = $laporan->count();
        $totalQtyTerjual = (int) $laporan->sum('total_terjual');

        return view('pengerajin.laporan_usaha.produk_slow_moving', compact(
            'usahaList',
            'kategoriList',
            'laporan',
            'start',
            'end',
            'threshold',
            'totalProdukSlow',
            'totalQtyTerjual',
            'pengerajinList'
        ));
    }

    public function exportProdukSlowMoving(Request $request)
    {
        $threshold = 5;

        [$startCarbon, $endCarbon] = $this->resolveDateRange($request, 30);
        $start = $startCarbon ? $startCarbon->toDateString() : null;
        $end = $endCarbon ? $endCarbon->toDateString() : null;

        $rows = $this->baseProdukSlowMovingQueryPengerajin($request, $start, $end, $threshold)->get();

        $export = new SimpleCollectionExport(
            $rows->map(fn($row) => [
                $row->nama_usaha,
                $row->nama_produk,
                $row->total_terjual,
                $row->transaksi_terakhir,
            ]),
            ['Usaha', 'Nama Produk', 'Total Terjual', 'Transaksi Terakhir']
        );

        $filename = 'produk_slow_moving_pengerajin_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

    // =========================================================================
    // PRODUK TERLARIS
    // =========================================================================
    protected function baseProdukTerlarisQueryPengerajin(Request $request): Builder
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                $join->on('map.usaha_id', '=', 'up.usaha_id')
                    ->where('map.pengerajin_id', '=', $pengerajinId);
            })
            ->whereIn('up.usaha_id', $usahaIds);

        [$start, $end] = $this->resolveDateRange($request, null);

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

    public function produkTerlaris(Request $request)
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $pengerajinList = collect([Auth::user()]);

        $laporan = $this->baseProdukTerlarisQueryPengerajin($request)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, SUM(oi.quantity) as total_terjual')
            ->orderByDesc('total_terjual')
            ->get();

        $totalProduk = $laporan->count();
        $totalTerjual = (int) $laporan->sum('total_terjual');
        $topRow = $laporan->first();
        $chartData = $laporan->take(10)->values();

        return view('pengerajin.laporan_usaha.produk_terlaris', compact(
            'usahaList',
            'kategoriList',
            'laporan',
            'totalProduk',
            'totalTerjual',
            'topRow',
            'chartData',
            'pengerajinList'
        ));
    }

    public function exportProdukTerlaris(Request $request)
    {
        $rows = $this->baseProdukTerlarisQueryPengerajin($request)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, SUM(oi.quantity) as total_terjual')
            ->orderByDesc('total_terjual')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(fn($row) => [
                $row->nama_produk,
                $row->total_terjual,
            ]),
            ['Nama Produk', 'Total Terjual']
        );

        $filename = 'produk_terlaris_pengerajin_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

    // =========================================================================
    // PRODUK VIEWS
    // =========================================================================
    protected function baseProdukViewsQueryPengerajin(Request $request): Builder
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $viewsQuery = DB::table('produk as p')
            ->join('produk_views as pv', 'pv.produk_id', '=', 'p.id')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                $join->on('map.usaha_id', '=', 'up.usaha_id')
                    ->where('map.pengerajin_id', '=', $pengerajinId);
            })
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->whereIn('up.usaha_id', $usahaIds);

        [$start, $end] = $this->resolveDateRange($request, null);

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

    public function produkViews(Request $request)
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $pengerajinList = collect([Auth::user()]);

        $totalProduk = DB::table('produk as p')
            ->whereExists(function ($q) use ($pengerajinId) {
                $q->select(DB::raw(1))
                    ->from('usaha_produk as up')
                    ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                        $join->on('map.usaha_id', '=', 'up.usaha_id')
                            ->where('map.pengerajin_id', '=', $pengerajinId);
                    })
                    ->whereColumn('up.produk_id', 'p.id');
            })
            ->distinct('p.id')
            ->count('p.id');

        $produkViews = $this->baseProdukViewsQueryPengerajin($request)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(pv.id) as total_views')
            ->orderByDesc('total_views')
            ->get();

        $produkDenganViews = $produkViews->count();
        $totalViews = (int) $produkViews->sum('total_views');

        return view('pengerajin.laporan_usaha.produk_views', compact(
            'usahaList',
            'totalProduk',
            'produkDenganViews',
            'totalViews',
            'produkViews',
            'pengerajinList'
        ));
    }

    public function exportProdukViews(Request $request)
    {
        $rows = $this->baseProdukViewsQueryPengerajin($request)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(pv.id) as total_views')
            ->orderByDesc('total_views')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(fn($row) => [
                $row->nama_produk,
                $row->total_views,
            ]),
            ['Nama Produk', 'Total Views']
        );

        $filename = 'produk_views_pengerajin_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

    // =========================================================================
    // TRANSAKSI PER USER (PEMBELI)
    // =========================================================================
    protected function baseTransaksiUserQueryPengerajin(Request $request): Builder
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $query = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                $join->on('map.usaha_id', '=', 'up.usaha_id')
                    ->where('map.pengerajin_id', '=', $pengerajinId);
            })
            ->whereIn('up.usaha_id', $usahaIds);

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id')) {
            $query->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('k.id', $request->kategori_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }

        return $query;
    }

    public function transaksiUser(Request $request)
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $pengerajinList = collect([Auth::user()]);

        $laporan = $this->baseTransaksiUserQueryPengerajin($request)
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

        return view('pengerajin.laporan_usaha.transaksi_user', compact(
            'usahaList',
            'kategoriList',
            'laporan',
            'totalUser',
            'totalTransaksi',
            'totalBelanja',
            'pengerajinList'
        ));
    }

    public function exportTransaksiUser(Request $request)
    {
        $rows = $this->baseTransaksiUserQueryPengerajin($request)
            ->groupBy('u.id', 'u.username')
            ->selectRaw('
                u.username,
                COUNT(DISTINCT o.id) as total_transaksi,
                SUM(oi.quantity * oi.price_at_purchase) as total_belanja
            ')
            ->orderByDesc('total_belanja')
            ->get();

        $export = new SimpleCollectionExport(
            $rows->map(fn($row) => [
                $row->username,
                $row->total_transaksi,
                $row->total_belanja,
            ]),
            ['User', 'Total Transaksi', 'Total Belanja']
        );

        $filename = 'transaksi_user_pengerajin_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }

    // =========================================================================
    // SEMUA TRANSAKSI
    // =========================================================================
    protected function baseTransaksiQueryPengerajin(Request $request): Builder
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $base = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->join('usaha_pengerajin as map', function ($join) use ($pengerajinId) {
                $join->on('map.usaha_id', '=', 'up.usaha_id')
                    ->where('map.pengerajin_id', '=', $pengerajinId);
            })
            ->whereIn('up.usaha_id', $usahaIds);

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('status')) {
            $base->where('o.status', $request->status);
        }
        if ($request->filled('usaha_id')) {
            $base->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $base->where('k.id', $request->kategori_id);
        }
        if ($start) {
            $base->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $base->whereDate('o.created_at', '<=', $end);
        }

        return $base;
    }

    public function transaksi(Request $request)
    {
        [$pengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $statusList = ['baru', 'dibayar', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
        $pengerajinList = collect([Auth::user()]);

        $base = $this->baseTransaksiQueryPengerajin($request);

        // list user pembeli dalam scope usaha_pengerajin
        $userList = (clone $base)
            ->whereNotNull('u.id')
            ->select('u.id', 'u.username')
            ->distinct()
            ->orderBy('u.username')
            ->get();

        if ($request->filled('user_id')) {
            $base->where('o.user_id', $request->user_id);
        }

        $transaksi = (clone $base)
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

        return view('pengerajin.laporan_usaha.transaksi', compact(
            'usahaList',
            'kategoriList',
            'userList',
            'statusList',
            'transaksi',
            'totalTransaksi',
            'totalNominal',
            'pengerajinList'
        ));
    }
}
