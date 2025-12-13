<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoProduk extends Model
{
    protected $table = 'foto_produk';
    protected $fillable = [
        'kode_foto_produk',
        'produk_id',
        'file_foto_produk'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
