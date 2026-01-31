<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;

class ReturnedProducts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationLabel = 'Stock Retours';
    protected static ?string $title = 'Produits en Retour';

    protected static string $view = 'filament.pages.returned-products';

    public array $returnedItems = [];
    public float $totalValue = 0; // Prix de vente
    public float $totalInvestment = 0; // Prix d'achat
    public int $totalCount = 0;

    public function mount(): void
    {
        $this->calculateData();
    }

    public function calculateData(): void
    {
        $this->returnedItems = [];
        $this->totalValue = 0;
        $this->totalInvestment = 0;
        $this->totalCount = 0;

        // Fetch all known costs
        $productCosts = \App\Models\ProductCost::pluck('cost_price', 'name')->toArray();

        // Fetch all orders marked as 'returned'
        $orders = Order::where('status', 'returned')->get();

        foreach ($orders as $order) {
            $items = $order->items ?? [];
            if (is_string($items)) {
                $items = json_decode($items, true) ?? [];
            }

            foreach ($items as $item) {
                // Determine item selling price
                $price = floatval($item['price'] ?? 0);
                $qty = intval($item['quantity'] ?? 0);
                
                // Determine item COST price (from DB or 0)
                $productName = $item['name'] ?? 'Inconnu';
                $costPrice = floatval($productCosts[$productName] ?? 0);

                $lineTotal = $price * $qty;
                $lineInvestment = $costPrice * $qty;

                // Aggregate data
                $this->returnedItems[] = [
                    'image_url' => $item['image_url'] ?? null,
                    'name' => $productName,
                    'sku' => $item['sku'] ?? '-',
                    'order_name' => $order->name,
                    'quantity' => $qty,
                    'unit_price' => $price, // Vente
                    'cost_price' => $costPrice, // Achat
                    'total_price' => $lineTotal,
                    'total_investment' => $lineInvestment,
                ];

                // Update Globals
                $this->totalCount += $qty;
                $this->totalValue += $lineTotal;
                $this->totalInvestment += $lineInvestment;
            }
        }
    }

    public function updateCost(string $productName, $value): void
    {
        $value = floatval($value);
        
        // Save to DB
        \App\Models\ProductCost::updateOrCreate(
            ['name' => $productName],
            ['cost_price' => $value]
        );

        // Recalculate everything to update the UI
        $this->calculateData();
        
        \Filament\Notifications\Notification::make()
            ->title('Coût mis à jour')
            ->success()
            ->send();
    }
}
