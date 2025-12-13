<?php
// File: app/Helpers/CartHelper.php

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

if (!function_exists('cart_count')) {
    function cart_count()
    {
        // Ganti auth()->check() menjadi Auth::check()
        if (Auth::check()) {
            // Ganti auth()->id() menjadi Auth::id()
            return CartItem::whereHas('cart', function ($q) {
                $q->where('user_id', Auth::id());
            })->sum('quantity');
        }

        // guest: pakai session cart
        return collect(session('cart', []))->sum('quantity');
    }
}
