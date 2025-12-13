<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsahaProduk;
use App\Models\Usaha;
use App\Models\Produk;
use Illuminate\Http\Request;

class UsahaProdukController extends Controller
{
    public function index()
    {
        return view('admin.usaha_produk.index-usaha_produk', [
            'usahaProduks' => UsahaProduk::all()
        ]);
    }

    public function create()
    {
        return view('admin.usaha_produk.create-usaha_produk', [
            'usahas' => Usaha::with('produks.pengerajin')->get(),
        ]);
    }

    public function edit($id)
    {
        $usahaProduk = UsahaProduk::findOrFail($id);
        return view('admin.usaha_produk.edit-usaha_produk', [
            'usahaProduk' => $usahaProduk,
            'usahas' => Usaha::all(),
            'produks' => Produk::all()
        ]);
    }
    public function getProdukByUsaha($usahaId)
    {
        // 1. Validasi Usaha: Pastikan Usaha dengan ID tersebut ada (opsional tapi disarankan)
        $usaha = Usaha::with('pengerajins')->find($usahaId);

        if (!$usaha) {
            // Jika Usaha tidak ditemukan, kembalikan response 404 atau array kosong
            return response()->json([], 404);
        }

        $pengerajinIds = $usaha->pengerajins->pluck('id');

        if ($pengerajinIds->isEmpty()) {
            return response()->json([]);
        }

        // 2. Ambil data Produk
        // **PENTING:** Ganti logika relasi di bawah ini agar sesuai dengan struktur database Anda.

        /*
         * Asumsi Relasi:
         * ----------------------------------------------------------------------
         * Kemungkinan A (Usaha memiliki banyak Produk):
         * Produk memiliki foreign key 'usaha_id'. (Paling umum jika 1 usaha hanya punya produknya sendiri)
         *
         * Kemungkinan B (Many-to-Many):
         * Terdapat tabel pivot (misalnya 'usaha_produk') yang menghubungkan Usaha dan Produk. (Paling umum jika banyak usaha bisa menjual banyak produk)
         * ----------------------------------------------------------------------
         */

        // --- SOLUSI untuk Kemungkinan A (Produk memiliki foreign key usaha_id) ---
        $produks = Produk::whereIn('pengerajin_id', $pengerajinIds)
                         ->select('id', 'nama_produk') // Ambil hanya kolom yang dibutuhkan oleh dropdown
                         ->orderBy('nama_produk')
                         ->get();

        /* // --- SOLUSI untuk Kemungkinan B (Many-to-Many, melalui relasi 'produks' di model Usaha) ---
        $produks = $usaha->produks()
                         ->select('produks.id', 'produks.nama_produk')
                         ->get();
        */


        // 3. Kembalikan data dalam format JSON
        // Data harus berupa array objek dengan 'id' dan 'nama_produk' agar sesuai dengan script JS
        return response()->json($produks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usaha_id' => 'required|exists:usaha,id',
            'produk_id' => 'required|exists:produk,id',
        ]);

        UsahaProduk::create($request->all());

        return redirect()->route('admin.usaha_produk-index')
            ->with('success', 'Usaha Produk berhasil ditambahkan.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'usaha_id' => 'required|exists:usaha,id',
            'produk_id' => 'required|exists:produk,id',
        ]);

        $usahaProduk = UsahaProduk::findOrFail($request->id);
        $usahaProduk->update($request->all());

        return redirect()->route('admin.usaha_produk-index')
            ->with('success', 'Usaha Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $usahaProduk = UsahaProduk::findOrFail($id);
        $usahaProduk->delete();

        return redirect()->route('admin.usaha_produk-index')
            ->with('success', 'Usaha Produk berhasil dihapus.');
    }
}
