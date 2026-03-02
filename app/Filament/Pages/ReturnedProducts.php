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

    public string $selectedProductName = '';

    public int $selectedAvailable = 0;

    // Tracks which order+item pairs are selected for delete
    public array $selectedSources = [];

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
        $this->dispatch('open-modal', id: 'delete-modal');
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

        if (empty($this->selectedSources)) {
            Notification::make()
                ->title('Erreur: aucune source selectionnee')
                ->danger()
                ->send();

            return;
        }

        $this->decreaseReturnedStock($this->selectedSources, $this->deleteQuantity);

        $this->dispatch('close-modal', id: 'delete-modal');
        $this->calculateData();

        Notification::make()
            ->title('Produits supprimes du stock')
            ->success()
            ->send();
    }

    /**
     * Decrease returned stock by marking items as removed on their source orders.
     * Uses FIFO across the source orders.
     */
    private function decreaseReturnedStock(array $sources, int $quantity): void
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

            $order->markReturnedItemRemoved($source['item_index'], $consume);

            $remaining -= $consume;
        }
    }
}
