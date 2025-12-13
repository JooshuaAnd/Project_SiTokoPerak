<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper buat badge status di Blade
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'baru'       => 'bg-warning',
            'dibayar'    => 'bg-info',
            'diproses'   => 'bg-primary',
            'dikirim'    => 'bg-success',
            'selesai'    => 'bg-success',
            'dibatalkan' => 'bg-danger',
        ];

        return $badges[$this->status] ?? 'bg-secondary';
    }
}
