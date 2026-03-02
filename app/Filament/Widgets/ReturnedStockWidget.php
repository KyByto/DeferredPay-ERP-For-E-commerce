<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ReturnedProducts;
use App\Models\Order;
use Filament\Widgets\Widget;

class ReturnedStockWidget extends Widget
{
    protected static string $view = 'filament.widgets.returned-stock-widget';

    protected function getViewData(): array
    {
        $totalCount = 0;
        $totalValue = 0;

        $returnedOrders = Order::where('status', 'returned')->get();

        foreach ($returnedOrders as $order) {
            $items = $order->items ?? [];
            $returnedSold = $order->returned_sold ?? [];

            foreach ($items as $index => $item) {
                $totalQty = (int) ($item['quantity'] ?? 0);
                $sold = (int) ($returnedSold[$index]['sold'] ?? 0);
                $removed = (int) ($returnedSold[$index]['removed'] ?? 0);
                $available = max(0, $totalQty - $sold - $removed);

                if ($available > 0) {
                    $totalCount += $available;
                    $totalValue += $available * (float) ($item['price'] ?? 0);
                }
            }
        }

        return [
            'totalCount' => $totalCount,
            'totalValue' => $totalValue,
            'manageUrl' => ReturnedProducts::getUrl(),
        ];
    }
}
