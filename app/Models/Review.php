<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'produk_id',
        'user_id',
        'rating',
        'komentar',
    ];

    /**
     * Get the user that owns the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that the review belongs to.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Get the media associated with the review.
     */
    public function media()
    {
        return $this->hasMany(ReviewMedia::class);
    }
}
