<?php

namespace App\Http\Controllers\Pengerajin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengerajin;

class ProfilePengerajinController extends Controller
{
    public function profile()
    {
        $pengerajin = Pengerajin::where('user_id', Auth::id())->first();

        return view('pengerajin.profile.profile', [
            'title' => 'Profil Pengerajin',
            'pengerajin' => $pengerajin,
        ]);
    }
}
