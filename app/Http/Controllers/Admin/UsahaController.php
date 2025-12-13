<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsahaController extends Controller
{
    public function index()
    {
        $dataUsaha = Usaha::all(); // atau bisa juga pakai paginate()

        return view('admin.usaha.index-usaha', [
            'usahas' => $dataUsaha
        ]);
    }

    public function create()
    {
        return view('admin.usaha.create-usaha');
    }

    public function edit($id)
    {
        $usaha = Usaha::findOrFail($id);
        return view('admin.usaha.edit-usaha', compact('usaha'));
    }

    public function store(Request $request)
    {
        //dd($request->all());

        $request->validate([
            'kode_usaha' => 'required|string|max:255',
            'nama_usaha' => 'required|string|max:255',
            'telp_usaha' => 'required|string|max:15',
            'email_usaha' => 'required|email|max:255',
            'deskripsi_usaha' => 'nullable|string',
            'foto_usaha' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link_gmap_usaha' => 'nullable|url',
            'status_usaha' => 'required|in:aktif,nonaktif,tutup,pending,dibekukan',
        ]);

        // Simpan data foto produk ke database
        if ($request->hasFile('foto_usaha')) {
            // Mendapatkan nama asli file
            $originalName = $request->file('foto_usaha')->getClientOriginalName();

            // Menyimpan file dengan nama asli
            $path = $request->file('foto_usaha')->storeAs('foto_usaha', $originalName, 'public');

            // Mengupdate data dengan path relatif
            $data['foto_usaha'] = $path;

            // Simpan data foto produk ke database
            Usaha::create([
                'kode_usaha' => $request->kode_usaha,
                'nama_usaha' => $request->nama_usaha,
                'telp_usaha' => $request->telp_usaha,
                'email_usaha' => $request->email_usaha,
                'deskripsi_usaha' => $request->deskripsi_usaha,
                'foto_usaha' => $data['foto_usaha'],
                'link_gmap_usaha' => $request->link_gmap_usaha,
                'status_usaha' => $request->status_usaha,
            ]);

            // Redirect setelah berhasil
            return redirect()->route('admin.usaha-index')
                ->with('success', 'Usaha berhasil disimpan.');
        } else {
            return back()
                ->with('error', 'File foto tidak ditemukan.');
        }
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $data = $request->validate([
            'kode_usaha' => 'required|string|max:255',
            'nama_usaha' => 'required|string|max:255',
            'telp_usaha' => 'required|string|max:15',
            'email_usaha' => 'required|email|max:255',
            'deskripsi_usaha' => 'nullable|string',
            'foto_usaha' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link_gmap_usaha' => 'nullable|url',
            'status_usaha' => 'required|in:aktif,nonaktif,tutup,pending,dibekukan',
        ]);


        // Temukan usaha yang akan diupdate
        $usaha = Usaha::findOrFail($id);

        // Periksa apakah ada foto yang diupload
        if ($request->hasFile('foto_usaha')) {
            // Hapus foto lama jika ada
            if ($usaha->foto_usaha) {
                Storage::disk('public')->delete($usaha->foto_usaha);
            }

            // Mendapatkan nama asli file
            $originalName = $request->file('foto_usaha')->getClientOriginalName();

            // Menyimpan file dengan nama asli
            $path = $request->file('foto_usaha')->storeAs('foto_usaha', $originalName, 'public');

            $data['foto_usaha'] = $path;
        } else {
            // Jika tidak ada foto baru, gunakan foto lama
            $data['foto_usaha'] = $usaha->foto_usaha;
        }

        // Update data usaha
        $usaha->update($data);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.usaha-index')
            ->with('success', 'Usaha berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $usaha = Usaha::findOrFail($id);
        if ($usaha->foto_usaha) {
            Storage::disk('public')->delete($usaha->foto_usaha);
        }
        $usaha->delete();

        return redirect()->route('admin.usaha-index')
            ->with('success', 'Usaha berhasil dihapus.');
    }
}
