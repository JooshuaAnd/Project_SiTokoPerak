<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukView;

class ProdukViewController extends Controller
{
    public function store(Request $request, $produkId)
    {
        ProdukView::create([
            'produk_id' => $produkId,
            'session_id' => session()->getId(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
