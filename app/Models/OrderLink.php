<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLink extends Model
{
    protected $fillable = ['order_id', 'token', 'used'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
