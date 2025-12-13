<?php

namespace App\Http\Controllers\Pengerajin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Models\UsahaPengerajin;;
use App\Models\UsahaProduk;

class DashboardPengerajinController extends Controller
{
    public function dashboard()
    {
        $pengerajin = Auth::user()->pengerajin;

        $pengerajinId = $pengerajin->id;

           // Cari usaha yang dimiliki oleh pengerajin
        $usahaId = UsahaPengerajin::where('pengerajin_id', $pengerajinId)
            ->value('usaha_id');

        if (!$usahaId) {
            return view('pengerajin.dashboard.dashboard', ['produk' => collect()]);
        }

        // Cari semua produk dalam usaha tersebut
        $produkIds = UsahaProduk::where('usaha_id', $usahaId)->pluck('produk_id');

        $produk = Produk::whereIn('id', $produkIds)->get();

        return view('pengerajin.layouts.pengerajin', compact('produk'));
    }

    public function profile()
    {
        return view('pengerajin.profile');
    }

    public function produk()
    {
        $pengerajin = Auth::user()->pengerajin;

        $pengerajinId = $pengerajin->id;

           // Cari usaha yang dimiliki oleh pengerajin
        $usahaId = UsahaPengerajin::where('pengerajin_id', $pengerajinId)
            ->value('usaha_id');

        if (!$usahaId) {
            return view('pengerajin.dashboard', ['produk' => collect()]);
        }

        // Cari semua produk dalam usaha tersebut
        $produkIds = UsahaProduk::where('usaha_id', $usahaId)->pluck('produk_id');

        $produk = Produk::whereIn('id', $produkIds)->get();
        return view('pengerajin.produk.produk',);
    }

}
