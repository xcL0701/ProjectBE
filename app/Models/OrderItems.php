<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'unit_price',
        'quantity',
        'total_price',
    ];

    // Relasi: Item pesanan milik satu pesanan
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi: Item pesanan milik satu produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    protected static function booted(): void
    {
        static::creating(function ($item) {
            $item->total_price = $item->unit_price * $item->quantity;
        });
    }
}
