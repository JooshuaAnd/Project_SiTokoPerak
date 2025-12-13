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
    ];

    public function usahaPengerajin()
    {
        return $this->hasMany(UsahaPengerajin::class, 'pengerajin_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
