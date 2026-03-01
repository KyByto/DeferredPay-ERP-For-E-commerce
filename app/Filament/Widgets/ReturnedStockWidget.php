<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ReturnedProducts;
use App\Models\ReturnedProduct;
use Filament\Widgets\Widget;

class ReturnedStockWidget extends Widget
{
    protected static string $view = 'filament.widgets.returned-stock-widget';

    protected function getViewData(): array
    {
        $items = ReturnedProduct::where('status', 'en_stock')->get();
        $totalCount = (int) $items->sum('quantity');
        $totalValue = (float) $items->sum(fn ($item) => $item->quantity * $item->unit_price);

        return [
            'totalCount' => $totalCount,
            'totalValue' => $totalValue,
            'manageUrl' => ReturnedProducts::getUrl(),
        ];
    }
}
