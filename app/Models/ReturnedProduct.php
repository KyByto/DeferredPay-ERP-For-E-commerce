<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnedProduct extends Model
{
    protected $fillable = [
        'order_id',
        'product_name',
        'sku',
        'image_url',
        'unit_price',
        'quantity',
        'status',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
