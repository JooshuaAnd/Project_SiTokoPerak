<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\KategoriProduk;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    /**
     * LIST PRODUK YANG SUDAH DI-APPROVE
     */
    public function index()
    {
        // cuma produk yang sudah di-ACC
        $dataProduk = Produk::where('status', 'approved')->with('pengerajin')
            ->latest()
            ->get();

        return view('admin.produk.index-produk', [
            'produks' => $dataProduk
        ]);
    }

    /**
     * LIST PRODUK PENDING (MENUNGGU ACC)
     */
    public function pending()
    {
        $dataProduk = Produk::where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.produk.produk-pending', [
            'produks' => $dataProduk
        ]);
    }

    /**
     * ADMIN MENG-APPROVE PRODUK
     */
    public function approve($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->update(['status' => 'approved']);

        return back()->with('success', 'Produk berhasil di-ACC.');
    }

    /**
     * ADMIN MENOLAK PRODUK
     */
    public function reject($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->update(['status' => 'rejected']);

        return back()->with('success', 'Produk berhasil ditolak.');
    }

    /**
     * FORM CREATE
     */
    public function create()
    {
        $kategoriProduks = KategoriProduk::all();

        return view('admin.produk.create-produk', [
            'kategoriProduks' => $kategoriProduks,
            'pengerajins' => \App\Models\Pengerajin::all(),
        ]);

    }

    /**
     * FORM EDIT
     */
    public function edit($id)
    {
        $kategoriProduks = KategoriProduk::all();
        $produk = Produk::findOrFail($id);

        return view('admin.produk.edit-produk', [
            'kategoriProduks' => $kategoriProduks,
            'produk' => $produk
        ]);
    }

    /**
     * SIMPAN PRODUK BARU (BUATAN ADMIN)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_produk' => 'required|string',
            'kategori_produk_id' => 'required|exists:kategori_produk,id',
            'nama_produk' => 'required|string',
            'deskripsi' => 'required|string',
            'harga' => 'required|integer',
            'stok' => 'required|integer',
            'pengerajin_id'=> 'required',
        ]);

        // slug otomatis
        $data['slug'] = Str::slug($data['nama_produk']);

        // produk yang dibuat dari admin panel langsung approved
        $data['status'] = 'approved';

        try {
        Produk::create($data);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return redirect()->route('admin.produk-index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * UPDATE PRODUK
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'kode_produk' => 'required|string',
            'kategori_produk_id' => 'required|exists:kategori_produk,id',
            'nama_produk' => 'required|string',
            'deskripsi' => 'required|string',
            'harga' => 'required|integer',
            'stok' => 'required|integer',
        ]);

        // slug otomatis dari nama produk
        $data['slug'] = Str::slug($data['nama_produk']);

        // kalau diupdate dari admin, pastikan tetap approved
        $data['status'] = 'approved';

        Produk::where('id', $id)->update($data);

        return redirect()->route('admin.produk-index')
            ->with('success', 'Data Produk berhasil diupdate.');
    }

    /**
     * HAPUS PRODUK
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();

        return redirect()->route('admin.produk-index')
            ->with('success', 'Data Produk berhasil dihapus.');
    }
}
