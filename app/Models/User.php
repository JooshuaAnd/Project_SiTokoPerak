<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        "username",
        "email",
        "password",
        "role", // ➔ Tambahkan ini
        "name",
        "phone",
        "address",
    ];

    public function isAdmin()
    {
        return $this->role === "admin";
    }
    public function isUser()
    {
        return $this->role === "guest";
    }

    /**
     * Get the reviews for the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    protected $hidden = ["password", "remember_token"];

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }
    public function pengerajin()
    {
        return $this->hasOne(Pengerajin::class, 'user_id', 'id');
    }

}
