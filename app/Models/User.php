<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relasi: User memiliki banyak pesanan (orders)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Relasi: User memiliki banyak likes
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Relasi: User memiliki satu keranjang (cart)
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
    public function likedProducts()
    {
        return $this->belongsToMany(Product::class, 'likes')->withTimestamps();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@csi.com');
    }
}
