<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\ReturnedProduct;
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

    public function calculateData(): void
    {
        $this->returnedItems = [];
        $this->totalValue = 0;
        $this->totalCount = 0;
        $returnedProducts = ReturnedProduct::with('order')
            ->where('status', 'en_stock')
            ->get();

        $grouped = $returnedProducts->groupBy('product_name');

        foreach ($grouped as $productName => $items) {
            $quantity = $items->sum('quantity');
            $unitPrice = (float) ($items->first()->unit_price ?? 0);
            $orderNames = $items->pluck('order.name')->filter()->unique()->values()->all();

            $this->returnedItems[] = [
                'name' => $productName,
                'sku' => $items->first()->sku ?? '-',
                'image_url' => $items->first()->image_url,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'orders' => $orderNames,
            ];

            $this->totalCount += $quantity;
            $this->totalValue += $quantity * $unitPrice;
        }
    }

    public function openSellModal(string $productName): void
    {
        $item = collect($this->returnedItems)->firstWhere('name', $productName);

        if (! $item) {
            return;
        }

        $this->selectedProductName = $item['name'];
        $this->selectedAvailable = $item['quantity'];
        $this->selectedUnitPrice = $item['unit_price'];
        $this->sellQuantity = 1;
        $this->sellPrice = $item['unit_price'];
        $this->sellClient = '';
        $this->sellPhone = '';
        $this->sellCanal = 'whatsapp';
        $this->sellModalOpen = true;
    }

    public function openDeleteModal(string $productName): void
    {
        $item = collect($this->returnedItems)->firstWhere('name', $productName);

        if (! $item) {
            return;
        }

        $this->selectedProductName = $item['name'];
        $this->selectedAvailable = $item['quantity'];
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

        $items = [[
            'name' => $this->selectedProductName,
            'quantity' => $this->sellQuantity,
            'price' => $this->sellPrice,
        ]];

        $subtotal = $this->sellQuantity * $this->sellPrice;

        $order = Order::create([
            'shopify_id' => 'msg-'.\Illuminate\Support\Str::uuid(),
            'name' => $this->generateMessageOrderName(),
            'email' => null,
            'total_price' => $subtotal,
            'subtotal_price' => $subtotal,
            'shipping_price' => 0,
            'status' => 'confirmed',
            'items' => $items,
            'order_date' => now(),
            'source' => 'messages',
            'canal_messages' => $this->sellCanal,
            'customer_name' => $this->sellClient,
            'customer_phone' => $this->sellPhone,
        ]);

        $this->decreaseReturnedStock($this->selectedProductName, $this->sellQuantity, 'vendu', "Commande {$order->name}");

        $this->sellModalOpen = false;
        $this->calculateData();

        Notification::make()
            ->title('Commande creee depuis stock')
            ->success()
            ->send();
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

        $this->decreaseReturnedStock($this->selectedProductName, $this->deleteQuantity, 'supprime', $this->deleteReason);

        $this->deleteModalOpen = false;
        $this->calculateData();

        Notification::make()
            ->title('Produits supprimes du stock')
            ->success()
            ->send();
    }

    private function decreaseReturnedStock(string $productName, int $quantity, string $status, ?string $notes = null): void
    {
        $remaining = $quantity;

        $records = ReturnedProduct::where('status', 'en_stock')
            ->where('product_name', $productName)
            ->orderBy('id')
            ->get();

        foreach ($records as $record) {
            if ($remaining <= 0) {
                break;
            }

            if ($record->quantity <= $remaining) {
                $record->update([
                    'status' => $status,
                    'notes' => $notes,
                ]);

                $remaining -= $record->quantity;

                continue;
            }

            $record->update([
                'quantity' => $record->quantity - $remaining,
            ]);

            ReturnedProduct::create([
                'order_id' => $record->order_id,
                'product_name' => $record->product_name,
                'sku' => $record->sku,
                'image_url' => $record->image_url,
                'unit_price' => $record->unit_price,
                'quantity' => $remaining,
                'status' => $status,
                'notes' => $notes,
            ]);

            $remaining = 0;
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
