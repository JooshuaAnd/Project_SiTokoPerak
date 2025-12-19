<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukLike;
use Illuminate\Support\Facades\Auth;

class ProdukLikeController extends Controller
{
    public function toggleLike(Request $request, $produkId)
    {
        $sessionId = session()->getId();

        // Get the authenticated user's ID
        $userId = Auth::id();

        // If no user is authenticated, use the session ID as a fallback for guest likes
        // This might need further consideration depending on whether guest likes are allowed or how they are tracked.
        // For now, we'll assume likes are tied to a logged-in user.
        $existing = ProdukLike::where('produk_id', $produkId)
            ->where('user_id', $userId) // Use user_id instead of session_id
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
            'user_id' => $userId, // Use user_id
        ]);

        return response()->json([
            'success' => true,
            'liked' => true,
            'totalLikes' => ProdukLike::where('produk_id', $produkId)->count(),
        ]);
    }
}
