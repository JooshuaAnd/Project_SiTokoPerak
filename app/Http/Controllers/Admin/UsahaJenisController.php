<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsahaJenis;
use App\Models\Usaha;
use App\Models\JenisUsaha;
use Illuminate\Http\Request;

class UsahaJenisController extends Controller
{
    public function index()
    {
        return view('admin.usaha_jenis.index-usaha_jenis', ([
            'usahaJeniss' => UsahaJenis::all()
        ]));
    }

    public function create()
    {
        return view('admin.usaha_jenis.create-usaha_jenis', [
            'usahas' => Usaha::all(),
            'jenisUsahas' => JenisUsaha::all()
        ]);
    }

    public function edit($id)
    {
        $usahaJenis = UsahaJenis::findOrFail($id);
        return view('admin.usaha_jenis.edit-usaha_jenis', [
            'usahaJenis' => $usahaJenis,
            'usahas' => Usaha::all(),
            'jenisUsahas' => JenisUsaha::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usaha_id' => 'required|exists:usaha,id',
            'jenis_usaha_id' => 'required|exists:jenis_usaha,id',
        ]);

        UsahaJenis::create($request->all());

        return redirect()->route('admin.usaha_jenis-index')
            ->with('success', 'Usaha Jenis berhasil ditambahkan.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'usaha_id' => 'required|exists:usaha,id',
            'jenis_usaha_id' => 'required|exists:jenis_usaha,id',
        ]);

        $usahaJenis = UsahaJenis::findOrFail($request->id);
        $usahaJenis->update($request->all());

        return redirect()->route('admin.usaha_jenis-index')
            ->with('success', 'Usaha Jenis berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        $usahaJenis = UsahaJenis::findOrFail($id);
        $usahaJenis->delete();

        return redirect()->route('admin.usaha_jenis-index')
            ->with('success', 'Usaha Jenis berhasil dihapus.');
    }
}
