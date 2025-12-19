<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukLike extends Model
{
    use HasFactory;

    protected $table = 'produk_likes';

    protected $fillable = ['produk_id', 'user_id'];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
