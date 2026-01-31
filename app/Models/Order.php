<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'shopify_id',
        'name',
        'email',
        'total_price',
        'subtotal_price',
        'shipping_price',
        'status',
        'items',
        'order_date',
    ];

    protected $casts = [
        'items' => 'array',
        'order_date' => 'datetime',
    ];
}
