<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usaha;
use App\Models\User;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Pengerajin;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengerajinExport;
use App\Exports\SimpleCollectionExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanUsahaController extends Controller
{
    /**
     * Helper untuk nentuin range tanggal dari:
     * - periode_type (day/week/month/year/custom)
     * - start_date & end_date (filter manual)
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
        }

        // fallback kalau semua kosong & ada default (misal 30 hari terakhir)
        if (!$startCarbon && !$endCarbon && $defaultLastDays) {
            $endCarbon = Carbon::now()->endOfDay();
            $startCarbon = $endCarbon->copy()->subDays($defaultLastDays - 1)->startOfDay();
        }

        return [$startCarbon, $endCarbon];
    }

    /**
     * Dashboard laporan utama
     * URL: /admin/laporan-usaha
     * Route name: admin.laporan_usaha.index
     */

    public function exportPdf(Request $request)
    {
        // Ambil filter yang sama seperti halaman transaksi kamu
        $usahaId = $request->usaha;     // sesuaikan nama param
        $kategori = $request->kategori;  // sesuaikan nama param
        $status = $request->status;    // sesuaikan nama param
        $start = $request->start;     // sesuaikan
        $end = $request->end;       // sesuaikan

        // TODO: samakan dengan query yang kamu pakai untuk tampilan transaksi / export excel
        // Contoh:
        // $data = Order::query()->...->get();

        $data = []; // ganti dengan data asli kamu
        $ringkasan = []; // ganti sesuai kebutuhan

        $pdf = Pdf::loadView('admin.laporan_usaha.transaksi_pdf', [
            'data' => $data,
            'ringkasan' => $ringkasan,
            'filters' => $request->all(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-transaksi.pdf');
    }

    public function index(Request $request)
    {
        // ---------- 1. DATA FILTER UNTUK DROPDOWN ----------
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
        // dropdown untuk admin pilih pengerajin
        $pengerajinList = Pengerajin::orderBy('nama_pengerajin')->get();

        // ---------- 2. RANGE TANGGAL DARI PERIODE ----------
        [$startDate, $endDate] = $this->resolveDateRange($request, null);

        // Label periode untuk ditampilkan di view
        $periodeLabel = null;
        if ($startDate && $endDate) {
            if ($startDate->isSameDay($endDate)) {
                $periodeLabel = $startDate->format('d/m/Y');
            } else {
                $periodeLabel = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
            }
        }

        // ---------- 3. BASE QUERY UTAMA ----------
// Rantai baru: orders -> order_items -> produk -> (p.pengerajin_id) -> usaha_pengerajin -> usaha
        $base = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('produk', 'produk.id', '=', 'order_items.produk_id')
            // produk -> pengerajin
            ->leftJoin('pengerajin', 'produk.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pengerajin_users', 'pengerajin.user_id', '=', 'pengerajin_users.id')
            // pengerajin -> usaha
            ->join('usaha_pengerajin as map', 'map.pengerajin_id', '=', 'produk.pengerajin_id')
            ->join('usaha', 'usaha.id', '=', 'map.usaha_id')
            // kategori & user pembeli
            ->leftJoin('kategori_produk', 'produk.kategori_produk_id', '=', 'kategori_produk.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id');

        // Hanya produk yang terikat pengerajin
        $base->whereNotNull('produk.pengerajin_id');


        // Filter tanggal dari periode
        if ($startDate) {
            $base->whereDate('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $base->whereDate('orders.created_at', '<=', $endDate);
        }

        // ---------- 4. FILTER TAMBAHAN DARI FORM ----------
        if ($request->filled('tahun')) {
            $base->whereYear('orders.created_at', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $base->whereMonth('orders.created_at', $request->bulan);
        }

        if ($request->filled('kategori_id')) {
            $base->where('kategori_produk.id', $request->kategori_id);
        }

        // filter baru: berdasarkan ID pengerajin (tabel pengerajin)
        if ($request->filled('pengerajin_id')) {
            $base->where('pengerajin.id', $request->pengerajin_id);
        }

        // filter lama: lewat akun user pengerajin (kalau masih dipakai di tempat lain)
        if ($request->filled('user_id')) {
            $base->where('pengerajin_users.id', $request->user_id);
        }

        if ($request->filled('usaha_id')) {
            $base->where('usaha.id', $request->usaha_id);
        }


        // Simpan baseQuery untuk dipakai di beberapa agregasi
        $baseQuery = clone $base;

        // ---------- 5. METRIC ATAS ----------
        $totalTransaksi = (clone $baseQuery)
            ->distinct('orders.id')
            ->count('orders.id');

        $totalPendapatan = (clone $baseQuery)
            ->selectRaw('SUM(order_items.quantity * order_items.price_at_purchase) as total')
            ->value('total') ?? 0;

        // ---------- 6. PENDAPATAN PER USAHA (TOP 3) ----------
        $baseUsahaGroup = clone $base;

        $pendapatanPerUsaha = (clone $baseUsahaGroup)
            ->selectRaw('usaha.id as usaha_id, usaha.nama_usaha, SUM(order_items.quantity * order_items.price_at_purchase) as total')
            ->whereNotNull('usaha.nama_usaha')
            ->groupBy('usaha.id', 'usaha.nama_usaha')
            ->orderByDesc('total')
            ->limit(3)
            ->get();


        $pendapatanChart = [
            'labels' => $pendapatanPerUsaha->pluck('nama_usaha'),
            'data' => $pendapatanPerUsaha->pluck('total'),
        ];

        // ---------- 6b. PENDAPATAN PER PENGERAJIN (TOP 3) ----------
        $pendapatanPerPengerajin = (clone $baseQuery)
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

        // ---------- 6c. PERFORMA PENJUALAN 5 USAHA TERATAS (LINE CHART) ----------
        $performaPenjualanChart = [
            'labels' => [],
            'datasets' => [],
        ];

        // Ambil 5 usaha dengan penjualan terbesar
        $topUsahaPerforma = (clone $baseUsahaGroup)
            ->selectRaw('usaha.id as usaha_id, usaha.nama_usaha, SUM(order_items.quantity * order_items.price_at_purchase) as total')
            ->whereNotNull('usaha.id')
            ->groupBy('usaha.id', 'usaha.nama_usaha')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        if ($topUsahaPerforma->count() > 0) {
            $usahaIds = $topUsahaPerforma->pluck('usaha_id')->all();

            $performaRaw = (clone $baseUsahaGroup)
                ->whereIn('usaha.id', $usahaIds)
                ->selectRaw('
                    usaha.id as usaha_id,
                    usaha.nama_usaha,
                    DATE(orders.created_at) as tgl,
                    SUM(order_items.quantity * order_items.price_at_purchase) as total
                ')
                ->groupBy('usaha.id', 'usaha.nama_usaha', 'tgl')
                ->orderBy('tgl')
                ->get();

            $labels = $performaRaw->pluck('tgl')->unique()->sort()->values()->all();

            $datasets = [];
            foreach ($topUsahaPerforma as $usaha) {
                $dataPerUsaha = [];
                foreach ($labels as $tgl) {
                    $row = $performaRaw->first(function ($item) use ($usaha, $tgl) {
                        return $item->usaha_id == $usaha->usaha_id && $item->tgl === $tgl;
                    });
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

        // ---------- 7. TOP PRODUK ----------
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

        // ---------- 8. TOP USER (Pembeli) ----------
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

        // ---------- 9. TOP KATEGORI ----------
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

        // ---------- 10. PRODUK FAVORITE & VIEWS ----------
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

        // ---------- 11. RETURN VIEW ----------
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
            'performaPenjualanChart',
            'produkTerlarisChart',
            'produkFavoriteChart',
            'produkViewChart',
            'transaksiUserChart',
            'kategoriChart',
            'topProduk',
            'userAktif',
            'periodeLabel',
        ));
    }

    /* =========================================================================
     * BASE QUERIES LAIN (dipakai halaman laporan lainnya)
     * ====================================================================== */

    protected function baseKategoriProdukQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('kategori_produk as k')
            ->leftJoin('produk as p', 'p.kategori_produk_id', '=', 'k.id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->leftJoin('order_items as oi', 'oi.produk_id', '=', 'p.id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pu', 'pengerajin.user_id', '=', 'pu.id');

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }
        if ($request->filled('pengerajin_id')) {
            $query->where('pengerajin.id', $request->pengerajin_id);
        }
        if ($request->filled('user_id')) {
            $query->where('pu.id', $request->user_id);
        }

        return $query;
    }

    protected function basePendapatanUsahaQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('produk as p', 'p.id', '=', 'oi.produk_id')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->join('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pu', 'pengerajin.user_id', '=', 'pu.id');

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('k.id', $request->kategori_id);
        }
        if ($request->filled('pengerajin_id')) {
            $query->where('pengerajin.id', $request->pengerajin_id);
        }
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

    protected function baseProdukFavoriteQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('produk as p')
            ->leftJoin('produk_likes as pl', 'pl.produk_id', '=', 'p.id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id');

        if ($request->filled('usaha_id')) {
            $query->where('u.id', $request->usaha_id);
        }
        if ($request->filled('pengerajin_id')) {
            $query->where('pengerajin.id', $request->pengerajin_id);
        }
        if ($start) {
            $query->whereDate('pl.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('pl.created_at', '<=', $end);
        }

        return $query;
    }

    protected function baseProdukSlowMovingQuery(Request $request, ?string $start, ?string $end, int $threshold)
    {
        $query = DB::table('produk as p')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('order_items as oi', function ($join) use ($start, $end) {
                $join->on('oi.produk_id', '=', 'p.id');
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
        if ($request->filled('pengerajin_id')) {
            $query->where('pengerajin.id', $request->pengerajin_id);
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

    protected function baseProdukTerlarisQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('produk as p', 'p.id', '=', 'oi.produk_id')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pu', 'pengerajin.user_id', '=', 'pu.id');

        if ($request->filled('usaha_id')) {
            $query->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('p.kategori_produk_id', $request->kategori_id);
        }
        if ($request->filled('pengerajin_id')) {
            $query->where('pengerajin.id', $request->pengerajin_id);
        }
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

    protected function baseProdukViewsQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $viewsQuery = DB::table('produk as p')
            ->join('produk_views as pv', 'pv.produk_id', '=', 'p.id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as u', 'u.id', '=', 'up.usaha_id')
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id');

        if ($request->filled('usaha_id')) {
            $viewsQuery->where('u.id', $request->usaha_id);
        }
        if ($request->filled('pengerajin_id')) {
            $viewsQuery->where('pengerajin.id', $request->pengerajin_id);
        }
        if ($start) {
            $viewsQuery->whereDate('pv.created_at', '>=', $start);
        }
        if ($end) {
            $viewsQuery->whereDate('pv.created_at', '<=', $end);
        }

        return $viewsQuery;
    }

    protected function baseTransaksiUserQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $query = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->join('produk as p', 'p.id', '=', 'oi.produk_id')
            ->join('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->join('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id');

        if ($request->filled('usaha_id')) {
            $query->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $query->where('p.kategori_produk_id', $request->kategori_id);
        }
        if ($request->filled('pengerajin_id')) {
            $query->where('pengerajin.id', $request->pengerajin_id);
        }
        if ($start) {
            $query->whereDate('o.created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('o.created_at', '<=', $end);
        }

        return $query;
    }

    protected function baseTransaksiQuery(Request $request, ?Carbon $start, ?Carbon $end)
    {
        $base = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->leftJoin('produk as p', 'p.id', '=', 'oi.produk_id')
            ->leftJoin('usaha_produk as up', 'up.produk_id', '=', 'p.id')
            ->leftJoin('usaha as us', 'us.id', '=', 'up.usaha_id')
            ->leftJoin('kategori_produk as k', 'k.id', '=', 'p.kategori_produk_id')
            ->leftJoin('pengerajin', 'p.pengerajin_id', '=', 'pengerajin.id')
            ->leftJoin('users as pu', 'pengerajin.user_id', '=', 'pu.id');

        if ($request->filled('usaha_id')) {
            $base->where('us.id', $request->usaha_id);
        }
        if ($request->filled('kategori_id')) {
            $base->where('k.id', $request->kategori_id);
        }
        if ($request->filled('pengerajin_id')) {
            $base->where('pengerajin.id', $request->pengerajin_id);
        }
        if ($request->filled('user_id')) {
            $base->where('pu.id', $request->user_id);
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

    // belum dipakai
    public function exportPengerajin()
    {
        return Excel::download(new PengerajinExport, 'pengerajin.xlsx');
    }

    public function pdfExportSimpleCollection()
    {
        $data = User::select('id', 'name', 'email', 'created_at')->limit(100)->get();

        $pdf = Pdf::loadView('admin.laporan_usaha.simple_collection_pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('simple-collection.pdf');
    }
}
