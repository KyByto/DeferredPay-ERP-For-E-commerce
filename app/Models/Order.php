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
        'returned_sold',
    ];

    protected $casts = [
        'items' => 'array',
        'returned_sold' => 'array',
        'order_date' => 'datetime',
    ];

    /**
     * Initialize returned_sold tracking when an order is marked as returned.
     * Sets sold=0 and removed=0 for each item in the order.
     */
    public function initReturnedSold(): void
    {
        $items = $this->items ?? [];
        $returnedSold = [];

        foreach ($items as $item) {
            $returnedSold[] = ['sold' => 0, 'removed' => 0];
        }

        $this->update(['returned_sold' => $returnedSold]);
    }

    /**
     * Get the available (in stock) quantity for a specific item index.
     */
    public function getReturnedAvailable(int $itemIndex): int
    {
        $items = $this->items ?? [];
        $returnedSold = $this->returned_sold ?? [];

        if (! isset($items[$itemIndex])) {
            return 0;
        }

        $totalQty = (int) ($items[$itemIndex]['quantity'] ?? 0);
        $sold = (int) ($returnedSold[$itemIndex]['sold'] ?? 0);
        $removed = (int) ($returnedSold[$itemIndex]['removed'] ?? 0);

        return max(0, $totalQty - $sold - $removed);
    }

    /**
     * Mark some quantity of a returned item as sold.
     */
    public function markReturnedItemSold(int $itemIndex, int $quantity): void
    {
        $returnedSold = $this->returned_sold ?? [];

        if (! isset($returnedSold[$itemIndex])) {
            $returnedSold[$itemIndex] = ['sold' => 0, 'removed' => 0];
        }

        $returnedSold[$itemIndex]['sold'] = ($returnedSold[$itemIndex]['sold'] ?? 0) + $quantity;

        $this->update(['returned_sold' => $returnedSold]);
    }

    /**
     * Mark some quantity of a returned item as removed/deleted.
     */
    public function markReturnedItemRemoved(int $itemIndex, int $quantity): void
    {
        $returnedSold = $this->returned_sold ?? [];

        if (! isset($returnedSold[$itemIndex])) {
            $returnedSold[$itemIndex] = ['sold' => 0, 'removed' => 0];
        }

        $returnedSold[$itemIndex]['removed'] = ($returnedSold[$itemIndex]['removed'] ?? 0) + $quantity;

        $this->update(['returned_sold' => $returnedSold]);
    }
}
