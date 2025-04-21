<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'proof',
        'status',
        'paid_at',
    ];

    // Relasi: Pembayaran milik satu pesanan
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
