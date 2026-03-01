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
        'source',
        'canal_messages',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
    ];

    protected $casts = [
        'items' => 'array',
        'order_date' => 'datetime',
    ];

    public function returnedProducts()
    {
        return $this->hasMany(ReturnedProduct::class);
    }

    public function addReturnedProducts(): void
    {
        if ($this->returnedProducts()->exists()) {
            return;
        }

        $items = $this->items ?? [];

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($quantity <= 0) {
                continue;
            }

            $this->returnedProducts()->create([
                'product_name' => $item['name'] ?? 'Inconnu',
                'sku' => $item['sku'] ?? null,
                'image_url' => $item['image_url'] ?? null,
                'unit_price' => (float) ($item['price'] ?? 0),
                'quantity' => $quantity,
                'status' => 'en_stock',
            ]);
        }
    }
}
