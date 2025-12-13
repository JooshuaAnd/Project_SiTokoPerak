<?php

namespace App\Http\Controllers\Pengerajin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use App\Models\UsahaPengerajin;
use App\Models\UsahaProduk;
use App\Models\KategoriProduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdukPengerajinController extends Controller
{
    public function produk()
    {
        $pengerajin = Auth::user()->pengerajin;

        if (!$pengerajin) {
            return view('pengerajin.dashboard', ['produk' => collect()]);
        }

        $pengerajinId = $pengerajin->id;

        // usaha milik pengerajin
        $usahaId = UsahaPengerajin::where('pengerajin_id', $pengerajinId)->value('usaha_id');

        if (!$usahaId) {
            return view('pengerajin.dashboard', ['produk' => collect()]);
        }

        // semua produk di usaha tsb
        $produkIds = UsahaProduk::where('usaha_id', $usahaId)->pluck('produk_id');

        // hanya yang sudah di-ACC admin
        $produk = Produk::whereIn('id', $produkIds)
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('pengerajin.produk.produk', compact('produk'));
    }

    public function produk_all()
    {
        // semua produk approved (bukan hanya milik dia)
        $produk = Produk::where('status', 'approved')->latest()->get();

        return view('pengerajin.produk_all.produk_all', [
            'title' => 'Semua Produk',
            'produk' => $produk,
        ]);
    }

    public function create()
    {
        $kategori = KategoriProduk::all();
        return view('pengerajin.produk.create_produk', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required',
            'kode_produk' => 'required',
            'harga' => 'required|numeric',
            'stok' => 'required|numeric',
            'gambar' => 'required|image|max:2048',
            'deskripsi' => 'required',
            'kategori_produk_id' => 'required|exists:kategori_produk,id',
        ]);

        $pengerajin = Auth::user()->pengerajin;
        if (!$pengerajin) {
            return back()->with('error', 'Akun kamu belum terhubung ke data pengerajin.');
        }

        $pengerajinId = $pengerajin->id;

        $usahaId = UsahaPengerajin::where('pengerajin_id', $pengerajinId)->value('usaha_id');
        if (!$usahaId) {
            return back()->with('error', 'Kamu belum terdaftar pada usaha manapun. Hubungi admin.');
        }

        $data = $request->except('gambar');

        // relasi ke pengerajin
        $data['pengerajin_id'] = $pengerajinId;

        // slug otomatis
        $data['slug'] = Str::slug($data['nama_produk']);

        // produk baru = pending
        $data['status'] = 'pending';

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        DB::transaction(function () use ($data, $usahaId) {
            $produk = Produk::create($data);

            DB::table('usaha_produk')->insert([
                'usaha_id' => $usahaId,
                'produk_id' => $produk->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('pengerajin.produk')
            ->with('success', 'Produk berhasil diajukan dan menunggu ACC admin.');
    }

    public function edit($id)
    {
        $produk = Produk::findOrFail($id);
        $kategori = KategoriProduk::all();

        // opsional: pastikan ini produk usaha dia
        $this->authorizeProdukForCurrentPengerajin($produk->id);

        return view('pengerajin.produk.edit_produk', compact('produk', 'kategori'));
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);
        $this->authorizeProdukForCurrentPengerajin($produk->id);

        $request->validate([
            'nama_produk' => 'required',
            'kode_produk' => 'required',
            'harga' => 'required|numeric',
            'stok' => 'required|numeric',
            'gambar' => 'nullable|image|max:2048',
            'deskripsi' => 'required',
            'kategori_produk_id' => 'required|exists:kategori_produk,id',
        ]);

        $data = $request->except('gambar');

        // slug update otomatis
        $data['slug'] = Str::slug($data['nama_produk']);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        // kalau mau edit = pending lagi
        $data['status'] = 'pending';

        $produk->update($data);

        return redirect()->route('pengerajin.produk')
            ->with('success', 'Produk berhasil diperbarui dan menunggu ACC admin.');
    }

    public function delete($id)
    {
        $produk = Produk::findOrFail($id);
        $this->authorizeProdukForCurrentPengerajin($produk->id);

        $produk->delete();

        return redirect()->route('pengerajin.produk')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Pastikan produk ini memang milik usaha pengerajin yang login.
     */
    protected function authorizeProdukForCurrentPengerajin($produkId)
    {
        $user = Auth::user();
        $pengerajin = $user->pengerajin ?? null;
        if (!$pengerajin) {
            abort(403, 'Akses ditolak.');
        }

        $usahaId = UsahaPengerajin::where('pengerajin_id', $pengerajin->id)->value('usaha_id');

        $exists = UsahaProduk::where('usaha_id', $usahaId)
            ->where('produk_id', $produkId)
            ->exists();

        if (!$exists) {
            abort(403, 'Anda tidak berhak mengubah produk ini.');
        }
    }
}
