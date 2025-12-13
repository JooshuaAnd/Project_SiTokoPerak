<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Produk extends Model
{
    protected $table = "produk";
    protected $fillable = [
        "kode_produk",
        "kategori_produk_id",
        "nama_produk",
        "deskripsi",
        "harga",
        "stok",
        "slug",
        'status',
        'gambar',
        'pengerajin_id',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($produk) {
            $slug = Str::slug($produk->nama_produk);
            $existingSlugCount = self::where("slug", $slug)->count();
            if ($existingSlugCount > 0) {
                $slug .= "-" . ($existingSlugCount + 1);
            }
            $produk->slug = $slug;
        });

        static::updating(function ($produk) {
            if ($produk->isDirty("nama_produk")) {
                $slug = Str::slug($produk->nama_produk);
                $existingSlugCount = self::where("slug", $slug)
                    ->where("id", "!=", $produk->id)
                    ->count();
                if ($existingSlugCount > 0) {
                    $slug .= "-" . ($existingSlugCount + 1);
                }
                $produk->slug = $slug;
            }
        });
    }

    public function kategoriProduk()
    {
        return $this->belongsTo(KategoriProduk::class, "kategori_produk_id");
    }

    public function fotoProduk()
    {
        return $this->hasMany(FotoProduk::class, "produk_id");
    }

    public function usahaProduk()
    {
        return $this->hasMany(UsahaProduk::class, "produk_id");
    }

    public function usaha()
    {
        return $this->belongsToMany(
            Usaha::class,
            "usaha_produk",
            "produk_id",
            "usaha_id",
        );
    }


    /**
     * REVIEW RELATIONS AND METHODS
     */

    /**
     * A product has many reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Accessor for the average rating.
     * Usage: $produk->average_rating
     */
    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg("rating"), 1);
    }

    /**
     * Accessor for the count of ratings for each star level.
     * Usage: $produk->rating_counts
     */
    public function getRatingCountsAttribute()
    {
        return $this->reviews()
            ->select("rating", DB::raw("count(*) as total"))
            ->groupBy("rating")
            ->pluck("total", "rating");
    }

    /**
     * Accessor for the count of reviews that have a comment.
     * Usage: $produk->reviews_with_comment_count
     */
    public function getReviewsWithCommentCountAttribute()
    {
        return $this->reviews()->whereNotNull("komentar")->count();
    }

    /**
     * Accessor for the count of reviews that have media.
     * Usage: $produk->reviews_with_media_count
     */
    public function getReviewsWithMediaCountAttribute()
    {
        return $this->reviews()->whereHas("media")->count();
    }
    // Relasi ke view
    public function views()
    {
        return $this->hasMany(ProdukView::class, 'produk_id');
    }

    // ⭐ FIX: Relasi ke likes (WAJIB NAMA likes)
    public function likes()
    {
        return $this->hasMany(ProdukLike::class, 'produk_id');
    }
    public function pengerajin()
    {
        return $this->belongsTo(Pengerajin::class, 'pengerajin_id');
    }
}
