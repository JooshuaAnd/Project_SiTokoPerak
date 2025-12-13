<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsahaPengerajin;
use App\Models\Usaha;
use App\Models\Pengerajin;
use Illuminate\Http\Request;

class UsahaPengerajinController extends Controller
{
    public function index()
    {
        return view('admin.usaha_pengerajin.index-usaha_pengerajin', [
            'usahaPengerajins' => UsahaPengerajin::all()
        ]);
    }

    public function create()
    {
        return view('admin.usaha_pengerajin.create-usaha_pengerajin', [
            'usahas' => Usaha::all(),
            'pengerajins' => Pengerajin::all()
        ]);
    }

    public function edit($id)
    {
        $usahaPengerajin = UsahaPengerajin::findOrFail($id);
        return view('admin.usaha_pengerajin.edit-usaha_pengerajin', [
            'usahaPengerajin' => $usahaPengerajin,
            'usahas' => Usaha::all(),
            'pengerajins' => Pengerajin::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usaha_id' => 'required|exists:usaha,id',
            'pengerajin_id' => 'required|exists:pengerajin,id',
        ]);

        if(UsahaPengerajin::where('pengerajin_id', $request->pengerajin_id)
            ->exists()) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'Pengerajin sudah berelasi dengan suatu usaha.'])
                ->withInput();
        }

        UsahaPengerajin::create($request->all());

        return redirect()->route('admin.usaha_pengerajin-index')
            ->with('success', 'Usaha Pengerajin berhasil ditambahkan.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'usaha_id' => 'required|exists:usaha,id',
            'pengerajin_id' => 'required|exists:pengerajin,id',
        ]);

        $usahaPengerajin = UsahaPengerajin::findOrFail($request->id);
        $usahaPengerajin->update($request->all());

        return redirect()->route('admin.usaha_pengerajin-index')
            ->with('success', 'Usaha Pengerajin berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $usahaPengerajin = UsahaPengerajin::findOrFail($id);
        $usahaPengerajin->delete();

        return redirect()->route('admin.usaha_pengerajin-index')
            ->with('success', 'Usaha Pengerajin berhasil dihapus.');
    }


}
