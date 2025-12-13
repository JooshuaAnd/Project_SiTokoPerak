<?php
// \\\app/Helpers/CartHelper.php

use Illuminate\Support\Facades\DB;
use App\Models\CartItem;

if (!function_exists('cart_count')) {
    function cart_count()
    {
        if (auth()->check()) {
            return CartItem::whereHas('cart', function ($q) {
                $q->where('user_id', auth()->id());
            })->sum('quantity');
        }

        // guest: pakai session cart
        return collect(session('cart', []))->sum('quantity');
    }
}
