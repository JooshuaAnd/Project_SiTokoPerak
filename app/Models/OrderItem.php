<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
    // public function usahaProduk()
    // {
    //     // relasi utama: baris ini milik baris mana di usaha_produk
    //     return $this->belongsTo(UsahaProduk::class, 'usaha_produk_id');
    // }

    public function usaha()
    {
        // supaya bisa akses cepat: $orderItem->usaha
        return $this->hasOneThrough(
            Usaha::class,        // model tujuan
            UsahaProduk::class,  // model perantara
            'id',                // PK di usaha_produk
            'id',                // PK di usaha
            // 'usaha_produk_id',   // FK di order_items → usaha_produk.id
            'usaha_id'           // FK di usaha_produk → usaha.id
        );
    }
}
