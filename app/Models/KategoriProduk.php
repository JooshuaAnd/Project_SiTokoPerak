<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KategoriProduk extends Model
{
    protected $table = 'kategori_produk';
    protected $fillable = [
        'kode_kategori_produk',
        'nama_kategori_produk',
        'slug',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($kategori) {
            $slug = Str::slug($kategori->nama_kategori_produk);
            $existingSlugCount = self::where('slug', $slug)->count();
            if ($existingSlugCount > 0) {
                $slug .= '-' . ($existingSlugCount + 1);
            }
            $kategori->slug = $slug;
        });

        static::updating(function ($kategori) {
            $kategori->slug = Str::slug($kategori->nama_kategori_produk);
        });
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'kategori_produk_id');
    }
}
