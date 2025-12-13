<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisUsaha; // Pastikan model JenisUsaha sudah dibuat

class JenisUsahaController extends Controller
{
    public function index()
    {
        // Ambil semua data jenis usaha dari database
        $dataJenisUsaha = JenisUsaha::all(); // atau bisa juga pakai paginate()
        // Kirim data ke view
        return view('admin.jenis_usaha.index-jenis_usaha', [
            'jenisUsahas' => $dataJenisUsaha
        ]);
    }

    public function create()
    {
        return view('admin.jenis_usaha.create-jenis_usaha');
    }

    public function edit($id)
    {
        // Ambil data jenis usaha berdasarkan ID
        $jenisUsaha = JenisUsaha::findOrFail($id);
        // Kirim data ke view
        return view('admin.jenis_usaha.edit-jenis_usaha', compact('jenisUsaha'));
    }

    public function store(Request $request)
    {
        // Validasi dan simpan data jenis usaha
        $request->validate([
            'kode_jenis_usaha' => 'required|string|max:255',
            'nama_jenis_usaha' => 'required|string|max:255',
        ]);

        // Simpan data ke database
        JenisUsaha::create($request->all());

        return redirect()->route('admin.jenis_usaha-index')->with('success', 'Jenis Usaha berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        // Validasi dan update data jenis usaha
        $data = $request->validate([
            'kode_jenis_usaha' => 'required|string|max:255',
            'nama_jenis_usaha' => 'required|string|max:255',
        ]);

        // Update data ke database
        //JenisUsaha::where('id', $id)->update($request->only(['kode_jenis_usaha', 'nama_jenis_usaha']));
        // Atau bisa juga menggunakan findOrFail
        // $jenisUsaha = JenisUsaha::findOrFail($id);
        // $jenisUsaha->update($request->all());
        // Jika menggunakan findOrFail, jangan lupa untuk meng-import model JenisUsaha

        JenisUsaha::where('id', $id)->update($data);

        return redirect()->route('admin.jenis_usaha-index')->with('success', 'Jenis Usaha berhasil diupdate.');
    }

    public function destroy($id)
    {
        $jenisUsaha = JenisUsaha::findOrFail($id);
        $jenisUsaha->delete();

        return redirect()->route('admin.jenis_usaha-index')->with('success', 'Jenis Usaha berhasil dihapus.');
    }
}
