<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengerajin extends Model
{
    protected $table = 'pengerajin';
    protected $fillable = [
        'user_id',
        'kode_pengerajin',
        'nama_pengerajin',
        'jk_pengerajin',
        'usia_pengerajin',
        'telp_pengerajin',
        'email_pengerajin',
        'alamat_pengerajin',
        'password',
    ];

    public function usahaPengerajin()
    {
        return $this->hasMany(UsahaPengerajin::class, 'pengerajin_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function produk()
    {
        return $this->hasMany(Produk::class, 'pengerajin_id');
    }
    public function usahas()
    {
        // Menggunakan tabel pivot 'usaha_pengerajin'
        return $this->belongsToMany(Usaha::class, 'usaha_pengerajin', 'pengerajin_id', 'usaha_id');
    }


}
