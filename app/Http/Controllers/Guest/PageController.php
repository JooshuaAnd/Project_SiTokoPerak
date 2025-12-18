<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    /**
     * Tambahkan likes_count, views_count, dan is_liked ke query produk.
     * - Jika user login: is_liked berdasarkan user_id
     * - Jika belum login: is_liked berdasarkan guest_id (pakai session()->getId())
     */
    private function withLikeViewAndIsLiked($query)
    {
        $userId  = Auth::id();
        $guestId = session()->getId();

        return $query
            ->withCount([
                'likes as likes_count',
                'views as views_count',
            ])
            ->when($userId, function ($q) use ($userId) {
                $q->withExists([
                    'likes as is_liked' => fn ($likeQ) => $likeQ->where('user_id', $userId),
                ]);
            }, function ($q) use ($guestId) {
                $q->withExists([
                    'likes as is_liked' => fn ($likeQ) => $likeQ->where('guest_id', $guestId),
                ]);
            });
    }

    public function index()
    {
        // Kategori untuk carousel
        $kategoris = KategoriProduk::all();

        // Produk terbaru (8 item)
        $randomProduks = $this->withLikeViewAndIsLiked(
            Produk::query()->with('fotoProduk')
        )
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
        $kategori = KategoriProduk::where('slug', $slug)->firstOrFail();

        $produks = $this->withLikeViewAndIsLiked(
            Produk::query()
                ->where('kategori_produk_id', $kategori->id)
                ->with('fotoProduk')
        )->get();

        return view('guest.pages.products', [
            'kategori' => $kategori,
            'produks' => $produks,
        ]);
    }

    public function katalog(Request $request)
    {
        $query = $this->withLikeViewAndIsLiked(
            Produk::query()->with('kategoriProduk', 'fotoProduk')
        );

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
        // Produk utama
        $produk = $this->withLikeViewAndIsLiked(
            Produk::query()->with(['fotoProduk', 'usaha'])
        )
            ->where('slug', $slug)
            ->firstOrFail();

        $reviews = $produk->reviews()
            ->latest()
            ->with('user', 'media')
            ->get();

        // Produk terkait
        $randomProduks = $this->withLikeViewAndIsLiked(
            Produk::query()->with('fotoProduk')
        )
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
