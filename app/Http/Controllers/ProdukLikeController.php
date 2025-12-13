<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukLike;

class ProdukLikeController extends Controller
{
    public function toggleLike(Request $request, $produkId)
    {
        $sessionId = session()->getId();

        $existing = ProdukLike::where('produk_id', $produkId)
            ->where('session_id', $sessionId)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'success' => true,
                'liked' => false,
                'totalLikes' => ProdukLike::where('produk_id', $produkId)->count(),
            ]);
        }

        ProdukLike::create([
            'produk_id' => $produkId,
            'session_id' => $sessionId,
        ]);

        return response()->json([
            'success' => true,
            'liked' => true,
            'totalLikes' => ProdukLike::where('produk_id', $produkId)->count(),
        ]);
    }
}
