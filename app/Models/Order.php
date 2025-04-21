<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'shipping_method',
        'total_price',
        'total_paid',
        'shipping_cost',
        'initial_payment',
        'status',
        'address',
        'note'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $appends = ['calculated_total_paid', 'remaining_amount', 'created_at_local'];

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'order_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function link()
    {
        return $this->hasOne(OrderLink::class, 'order_id', 'id');
    }

    public function getCalculatedTotalPaidAttribute(): int
    {
        return $this->payments()->where('status', 'approved')->sum('amount');
    }

    public function getRemainingAmountAttribute(): int
    {
        return max(0, $this->total_price - $this->calculated_total_paid);
    }

    public function getCreatedAtLocalAttribute(): string
    {
        return $this->created_at->timezone('Asia/Jakarta')->format('d F Y H:i');
    }
}
