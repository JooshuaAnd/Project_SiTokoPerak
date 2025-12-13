<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukView extends Model
{
    use HasFactory;

    protected $table = 'produk_views';

    protected $fillable = ['produk_id', 'session_id'];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
