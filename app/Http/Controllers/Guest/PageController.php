<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Usaha;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();

        // Kategori untuk carousel
        $kategoris = KategoriProduk::all();

        // Produk terbaru (8 item) + info like/view + apakah sudah dilike oleh session ini
        $randomProduks = Produk::with('fotoProduk')
            ->withCount([
                'likes as likes_count',
                'views as views_count',
            ])
            ->withExists([
                'likes as is_liked' => function ($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                },
            ])
            ->latest()
            ->take(8)
            ->get();

        return view('guest.pages.index', [
            'kategoris' => $kategoris,
            'randomProduks' => $randomProduks,
        ]);
    }

    public function productsByCategory($slug)
    {
        $sessionId = session()->getId();

        $kategori = KategoriProduk::where('slug', $slug)->firstOrFail();

        $produks = Produk::where('kategori_produk_id', $kategori->id)
            ->with('fotoProduk')
            ->withCount([
                'likes as likes_count',
                'views as views_count',
            ])
            ->withExists([
                'likes as is_liked' => function ($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                },
            ])
            ->get();

        return view('guest.pages.products', [
            'kategori' => $kategori,
            'produks' => $produks,
        ]);
    }

    public function katalog(Request $request)
    {
        // *** JANGAN DIUBAH – sesuai versi kamu yang sudah benar ***
        $sessionId = session()->getId();

        $query = Produk::with('kategoriProduk', 'fotoProduk')
            ->withCount([
                'likes as likes_count',
                'views as views_count',
            ])
            ->withExists([
                'likes as is_liked' => function ($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                },
            ]);

        // ---------- SEARCH ----------
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_produk', 'like', '%' . $searchTerm . '%')
                    ->orWhere('deskripsi', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('kategoriProduk', function ($kategoriQuery) use ($searchTerm) {
                        $kategoriQuery->where('nama_kategori_produk', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        // ---------- FILTER KATEGORI ----------
        if ($request->filled('kategori')) {
            $query->whereHas('kategoriProduk', function ($q) use ($request) {
                $q->where('slug', $request->kategori);
            });
        }

        // ---------- FILTER USAHA ----------
        if ($request->filled('usaha')) {
            $query->whereHas('usahaProduk.usaha', function ($q) use ($request) {
                $q->where('id', $request->usaha)
                    ->where('status_usaha', 'aktif');
            });
        }

        // ---------- FILTER HARGA ----------
        if ($request->filled('min_harga')) {
            $query->where('harga', '>=', $request->min_harga);
        }
        if ($request->filled('max_harga')) {
            $query->where('harga', '<=', $request->max_harga);
        }

        // ---------- SORT ----------
        $urutkan = $request->input('urutkan', 'terbaru');
        switch ($urutkan) {
            case 'harga-rendah':
                $query->orderBy('harga', 'asc');
                break;
            case 'harga-tinggi':
                $query->orderBy('harga', 'desc');
                break;
            case 'populer':
                // contoh: urutkan by likes terbanyak
                $query->orderBy('likes_count', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $produks = $query->paginate(12)->withQueryString();
        $kategoris = KategoriProduk::all();

        return view('guest.pages.katalog', [
            'produks' => $produks,
            'kategoris' => $kategoris,
        ]);
    }

    public function singleProduct($slug)
    {
        $sessionId = session()->getId();

        // Produk utama
        $produk = Produk::with(['fotoProduk', 'usaha'])
            ->withCount([
                'likes as likes_count',
                'views as views_count',
            ])
            ->withExists([
                'likes as is_liked' => function ($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                },
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $reviews = $produk->reviews()
            ->latest()
            ->with('user', 'media')
            ->get();

        // Produk terkait
        $randomProduks = Produk::with('fotoProduk')
            ->withCount([
                'likes as likes_count',
                'views as views_count',
            ])
            ->withExists([
                'likes as is_liked' => function ($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                },
            ])
            ->where('id', '!=', $produk->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('guest.pages.single-product', [
            'produk' => $produk,
            'reviews' => $reviews,
            'randomProduks' => $randomProduks,
        ]);
    }

    public function detailUsaha(Request $request, Usaha $usaha)
    {
        $usaha->load('pengerajins', 'produks');
        $previousProduct = null;

        if ($request->has('from_product')) {
            $previousProduct = Produk::where('slug', $request->from_product)->first();
        }

        return view('guest.pages.detail-usaha', [
            'usaha' => $usaha,
            'produks' => $usaha->produks,
            'previousProduct' => $previousProduct,
        ]);
    }

    public function about()
    {
        return view('guest.pages.about');
    }

    public function contact()
    {
        return view('guest.pages.contact');
    }
}
