<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ReturnedProducts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationLabel = 'Produits Retournés';

    protected static ?string $navigationGroup = 'Stock';

    protected static ?string $title = 'Stock Produits Retournés';

    protected static string $view = 'filament.pages.returned-products';

    public array $returnedItems = [];

    public float $totalValue = 0;

    public int $totalCount = 0;

    public bool $sellModalOpen = false;

    public bool $deleteModalOpen = false;

    public string $selectedProductName = '';

    public int $selectedAvailable = 0;

    public float $selectedUnitPrice = 0;

    // Tracks which order+item pairs are selected for sell/delete
    public array $selectedSources = [];

    public int $sellQuantity = 1;

    public float $sellPrice = 0;

    public string $sellClient = '';

    public string $sellPhone = '';

    public string $sellCanal = 'whatsapp';

    public int $deleteQuantity = 1;

    public string $deleteReason = '';

    public function mount(): void
    {
        $this->calculateData();
    }

    /**
     * Build the returned items list directly from orders with status=returned.
     * Groups by product name, calculates available stock (quantity - sold - removed).
     */
    public function calculateData(): void
    {
        $this->returnedItems = [];
        $this->totalValue = 0;
        $this->totalCount = 0;

        $returnedOrders = Order::where('status', 'returned')->get();

        // Collect all items with available stock, grouped by product name
        $grouped = [];

        foreach ($returnedOrders as $order) {
            $items = $order->items ?? [];
            $returnedSold = $order->returned_sold ?? [];

            foreach ($items as $index => $item) {
                $totalQty = (int) ($item['quantity'] ?? 0);
                $sold = (int) ($returnedSold[$index]['sold'] ?? 0);
                $removed = (int) ($returnedSold[$index]['removed'] ?? 0);
                $available = max(0, $totalQty - $sold - $removed);

                if ($available <= 0) {
                    continue;
                }

                $name = $item['name'] ?? 'Inconnu';

                if (! isset($grouped[$name])) {
                    $grouped[$name] = [
                        'name' => $name,
                        'sku' => $item['sku'] ?? '-',
                        'image_url' => $item['image_url'] ?? null,
                        'quantity' => 0,
                        'unit_price' => (float) ($item['price'] ?? 0),
                        'orders' => [],
                        // Track sources: which order_id + item_index have available stock
                        'sources' => [],
                    ];
                }

                $grouped[$name]['quantity'] += $available;

                if ($order->name && ! in_array($order->name, $grouped[$name]['orders'])) {
                    $grouped[$name]['orders'][] = $order->name;
                }

                $grouped[$name]['sources'][] = [
                    'order_id' => $order->id,
                    'item_index' => $index,
                    'available' => $available,
                ];
            }
        }

        foreach ($grouped as $item) {
            $this->returnedItems[] = $item;
            $this->totalCount += $item['quantity'];
            $this->totalValue += $item['quantity'] * $item['unit_price'];
        }
    }

    public function openSellModal(int $index): void
    {
        $item = $this->returnedItems[$index] ?? null;

        if (! $item) {
            return;
        }

        $this->selectedProductName = $item['name'];
        $this->selectedAvailable = $item['quantity'];
        $this->selectedUnitPrice = $item['unit_price'];
        $this->selectedSources = $item['sources'];
        $this->sellQuantity = 1;
        $this->sellPrice = $item['unit_price'];
        $this->sellClient = '';
        $this->sellPhone = '';
        $this->sellCanal = 'whatsapp';
        $this->sellModalOpen = true;
    }

    public function openDeleteModal(int $index): void
    {
        $item = $this->returnedItems[$index] ?? null;

        if (! $item) {
            return;
        }

        $this->selectedProductName = $item['name'];
        $this->selectedAvailable = $item['quantity'];
        $this->selectedSources = $item['sources'];
        $this->deleteQuantity = $item['quantity'];
        $this->deleteReason = '';
        $this->deleteModalOpen = true;
    }

    public function sellProduct(): void
    {
        if ($this->sellQuantity <= 0 || $this->sellQuantity > $this->selectedAvailable) {
            Notification::make()
                ->title('Quantite invalide')
                ->danger()
                ->send();

            return;
        }

        if ($this->sellPrice <= 0) {
            Notification::make()
                ->title('Prix invalide')
                ->danger()
                ->send();

            return;
        }

        $items = [[
            'name' => $this->selectedProductName,
            'quantity' => $this->sellQuantity,
            'price' => $this->sellPrice,
        ]];

        $subtotal = $this->sellQuantity * $this->sellPrice;

        try {
            // Create the sale order
            $order = Order::create([
                'shopify_id' => 'msg-'.\Illuminate\Support\Str::uuid(),
                'name' => $this->generateMessageOrderName(),
                'email' => null,
                'total_price' => $subtotal,
                'subtotal_price' => $subtotal,
                'shipping_price' => 0,
                'status' => 'delivered',
                'items' => $items,
                'order_date' => now(),
                'source' => 'messages',
                'canal_messages' => $this->sellCanal,
                'customer_name' => $this->sellClient,
                'customer_phone' => $this->sellPhone,
            ]);

            // Mark sold quantities on the source returned orders (FIFO)
            $this->decreaseReturnedStock($this->selectedSources, $this->sellQuantity, 'sold');

            // Record income
            $treasury = new \App\Services\TreasuryEngine;
            $treasury->addDeliveryCollection(
                amount: $order->total_price,
                description: "Commande {$order->name} livree"
            );

            $this->sellModalOpen = false;
            $this->calculateData();

            Notification::make()
                ->title('Produit vendu et encaissé')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors de la vente')
                ->danger()
                ->send();
        }
    }

    public function deleteProduct(): void
    {
        if ($this->deleteQuantity <= 0 || $this->deleteQuantity > $this->selectedAvailable) {
            Notification::make()
                ->title('Quantite invalide')
                ->danger()
                ->send();

            return;
        }

        $this->decreaseReturnedStock($this->selectedSources, $this->deleteQuantity, 'removed');

        $this->deleteModalOpen = false;
        $this->calculateData();

        Notification::make()
            ->title('Produits supprimes du stock')
            ->success()
            ->send();
    }

    /**
     * Decrease returned stock by marking items as sold or removed on their source orders.
     * Uses FIFO across the source orders.
     *
     * @param  array  $sources  Array of ['order_id' => int, 'item_index' => int, 'available' => int]
     * @param  int  $quantity  Total quantity to consume
     * @param  string  $type  'sold' or 'removed'
     */
    private function decreaseReturnedStock(array $sources, int $quantity, string $type): void
    {
        $remaining = $quantity;

        foreach ($sources as $source) {
            if ($remaining <= 0) {
                break;
            }

            $order = Order::find($source['order_id']);
            if (! $order) {
                continue;
            }

            $available = $source['available'];
            $consume = min($remaining, $available);

            if ($type === 'sold') {
                $order->markReturnedItemSold($source['item_index'], $consume);
            } else {
                $order->markReturnedItemRemoved($source['item_index'], $consume);
            }

            $remaining -= $consume;
        }
    }

    private function generateMessageOrderName(): string
    {
        $lastName = Order::where('source', 'messages')
            ->where('name', 'like', 'MSG-%')
            ->orderByDesc('id')
            ->value('name');

        $next = 1;

        if ($lastName && preg_match('/MSG-(\d+)/', $lastName, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        return 'MSG-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
