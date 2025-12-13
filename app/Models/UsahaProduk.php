<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsahaProduk extends Model
{
    protected $table = 'usaha_produk';
    protected $fillable = [
        'usaha_id',
        'produk_id',
    ];

    // Relasi ke Usaha
    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    // Relasi ke Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
