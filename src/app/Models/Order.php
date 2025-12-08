<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_amount',
        'discount_code',
        'discount_value',
        'status',
        'payment_method',
        'delivery_method',
        'delivery_address',
        'ordered_at',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'discount_value' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
