<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SimpleCollectionExport;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Builder;

use App\Models\Usaha;
use App\Models\KategoriProduk;
use App\Models\Pengerajin;
use App\Models\User;

class PengerajinLaporanController extends Controller
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
    // HELPER: RESOLVE FILTER PENGERAJIN (default login, bisa "Semua")
    // =========================================================================
    protected function resolveFilterPengerajinId(Request $request, int $loginPengerajinId, array $usahaIds): ?int
    {
        if ($request->filled('pengerajin_id')) {
            $id = (int) $request->pengerajin_id;

            // validasi: pengerajin yg dipilih memang rekan dalam usaha yg user login punya akses
            $allowed = DB::table('usaha_pengerajin')
                ->whereIn('usaha_id', $usahaIds)
                ->where('pengerajin_id', $id)
                ->exists();

            if (!$allowed)
                abort(403, 'Pengerajin tidak valid untuk usaha ini.');

            return $id;
        }

        // kalau user klik "Terapkan" tapi pilih semua (kosong)
        if ($request->has('apply_filters')) {
            return null;
        }

        // default pertama kali buka halaman: login
        // biar dropdown juga ke-select login tanpa ubah blade
        $request->merge(['pengerajin_id' => $loginPengerajinId]);
        return $loginPengerajinId;
    }

    /**
     * Apply filter pengerajin:
     * - kalau ada usaha_produk.pengerajin_id -> pakai itu
     * - else kalau ada produk.pengerajin_id -> pakai itu
     * - else fallback join usaha_pengerajin (WARNING: bisa tidak bedain data per pengerajin dalam 1 usaha)
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

        return $q->join('usaha_pengerajin as map', function ($join) use ($filterPengerajinId, $upAlias) {
            $join->on('map.usaha_id', '=', "$upAlias.usaha_id")
                ->where('map.pengerajin_id', '=', $filterPengerajinId);
        });
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
                    $weekInput = $request->input('periode_week'); // "2025-W09"
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

        if (!$startCarbon && !$endCarbon && $defaultLastDays) {
            $endCarbon = Carbon::now()->endOfDay();
            $startCarbon = $endCarbon->copy()->subDays($defaultLastDays - 1)->startOfDay();
        }

        return [$startCarbon, $endCarbon];
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

    public function pendapatanUsaha(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

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
        $avgTransaksiGlobal = $totalTransaksi > 0 ? (int) floor($totalPendapatan / $totalTransaksi) : 0;

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



    public function exportTransaksi(Request $request)
    {
        $base = $this->baseTransaksiQueryPengerajin($request);

        // optional kalau di halaman transaksi ada filter user_id
        if ($request->filled('user_id')) {
            $base->where('o.user_id', $request->user_id);
        }

        $rows = (clone $base)
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
            $rows->map(fn($row) => [
                $row->id,
                $row->username,
                $row->total,
                $row->tanggal_transaksi,
                $row->status,
            ]),
            ['ID Order', 'User', 'Total', 'Tanggal Transaksi', 'Status']
        );

        $filename = 'transaksi_pengerajin_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }


    // =========================================================================
    // KATEGORI PRODUK
    // =========================================================================
    protected function baseKategoriProdukQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('kategori_produk as k')
            ->leftJoin('produk as p', 'p.kategori_produk_id', '=', 'k.id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
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

    public function kategoriProduk(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

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
    // PRODUK FAVORITE
    // =========================================================================
    protected function baseProdukFavoriteQueryPengerajin(Request $request): Builder
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('produk as p')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('produk_likes as pl', 'pl.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->whereIn('up.usaha_id', $usahaIds);

        $query = $this->applyPengerajinFilter($query, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id'))
            $query->where('u.id', $request->usaha_id);
        if ($start)
            $query->whereDate('pl.created_at', '>=', $start);
        if ($end)
            $query->whereDate('pl.created_at', '<=', $end);

        return $query;
    }

    public function produkFavorite(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

        $laporan = $this->baseProdukFavoriteQueryPengerajin($request)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(DISTINCT pl.id) as total_like')
            ->orderByDesc('total_like')
            ->get();

        $totalProduk = (clone $this->baseProdukFavoriteQueryPengerajin($request))
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
            ->selectRaw('p.nama_produk, COUNT(DISTINCT pl.id) as total_like')
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

    public function produkSlowMoving(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $threshold = 5;

        [$startCarbon, $endCarbon] = $this->resolveDateRange($request, 30);
        $start = $startCarbon ? $startCarbon->toDateString() : null;
        $end = $endCarbon ? $endCarbon->toDateString() : null;

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

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
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();
        $filterPengerajinId = $this->resolveFilterPengerajinId($request, $loginPengerajinId, $usahaIds);

        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('usaha_produk as up', 'up.id', '=', 'oi.usaha_produk_id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->join('produk as p', 'p.id', '=', 'up.produk_id')
            ->whereIn('up.usaha_id', $usahaIds);

        $query = $this->applyPengerajinFilter($query, $filterPengerajinId, 'up', 'p');

        [$start, $end] = $this->resolveDateRange($request, null);

        if ($request->filled('usaha_id'))
            $query->where('us.id', $request->usaha_id);
        if ($request->filled('kategori_id'))
            $query->where('p.kategori_produk_id', $request->kategori_id);
        if ($start)
            $query->whereDate('o.created_at', '>=', $start);
        if ($end)
            $query->whereDate('o.created_at', '<=', $end);

        return $query;
    }

    public function produkTerlaris(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

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
        if ($start)
            $viewsQuery->whereDate('pv.created_at', '>=', $start);
        if ($end)
            $viewsQuery->whereDate('pv.created_at', '<=', $end);

        return $viewsQuery;
    }

    public function produkViews(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

        $produkViews = $this->baseProdukViewsQueryPengerajin($request)
            ->groupBy('p.id', 'p.nama_produk')
            ->selectRaw('p.nama_produk, COUNT(DISTINCT pv.id) as total_views')
            ->orderByDesc('total_views')
            ->get();

        $totalProduk = (clone $this->baseProdukViewsQueryPengerajin($request))
            ->distinct('p.id')
            ->count('p.id');

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
            ->selectRaw('p.nama_produk, COUNT(DISTINCT pv.id) as total_views')
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
    // TRANSAKSI PER USER
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
        if ($request->filled('kategori_id'))
            $query->where('k.id', $request->kategori_id);
        if ($start)
            $query->whereDate('o.created_at', '>=', $start);
        if ($end)
            $query->whereDate('o.created_at', '<=', $end);

        return $query;
    }

    public function transaksiUser(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

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
        if ($request->filled('kategori_id'))
            $base->where('k.id', $request->kategori_id);
        if ($start)
            $base->whereDate('o.created_at', '>=', $start);
        if ($end)
            $base->whereDate('o.created_at', '<=', $end);

        return $base;
    }

    public function transaksi(Request $request)
    {
        [$loginPengerajinId, $usahaIds] = $this->getPengerajinContext();

        $usahaList = Usaha::whereIn('id', $usahaIds)->get();
        $kategoriList = KategoriProduk::all();
        $statusList = ['baru', 'dibayar', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

        $selectedUsahaId = $request->filled('usaha_id') ? (int) $request->usaha_id : null;
        $pengerajinList = $this->getPengerajinListForFilter($usahaIds, $selectedUsahaId);

        $base = $this->baseTransaksiQueryPengerajin($request);

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
