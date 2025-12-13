<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usaha extends Model
{
    protected $table = 'usaha';
    protected $fillable = [
        'kode_usaha',
        'nama_usaha',
        'telp_usaha',
        'email_usaha',
        'deskripsi_usaha',
        'foto_usaha',
        'link_gmap_usaha',
        'status_usaha',
    ];

    // public function usahaJenis()
    // {
    //     return $this->hasMany(UsahaJenis::class, 'usaha_id');
    // }

    // public function usahaPengerajin()
    // {
    //     return $this->hasMany(UsahaPengerajin::class, 'usaha_id');
    // }

    public function pengerajins()
    {
        return $this->belongsToMany(Pengerajin::class, 'usaha_pengerajin', 'usaha_id', 'pengerajin_id');
    }

    public function jenisUsahas()
    {
        return $this->belongsToMany(JenisUsaha::class, 'usaha_jenis', 'usaha_id', 'jenis_usaha_id');
    }

    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'usaha_produk', 'usaha_id', 'produk_id');
    }
}
