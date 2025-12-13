<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsahaJenis extends Model
{
    protected $table = 'usaha_jenis';

    protected $fillable = [
        'usaha_id',
        'jenis_usaha_id',
    ];

    // Relasi ke Usaha
    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    // Relasi ke JenisUsaha
    public function jenisUsaha()
    {
        return $this->belongsTo(JenisUsaha::class, 'jenis_usaha_id');
    }
}
