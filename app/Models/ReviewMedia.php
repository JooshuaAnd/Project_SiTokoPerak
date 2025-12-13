<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewMedia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'review_id',
        'file_path',
        'file_type',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'review_media';

    /**
     * Get the review that owns the media.
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}
