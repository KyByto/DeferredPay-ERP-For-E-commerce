<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;

class MonthlyOrdersWidget extends Widget
{
    protected static string $view = 'filament.widgets.monthly-orders-widget';

    protected function getViewData(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $baseQuery = Order::whereBetween('order_date', [$start, $end]);

        $shopifyCount = (clone $baseQuery)->where('source', 'shopify')->count();
        $messagesCount = (clone $baseQuery)->where('source', 'messages')->count();
        $totalCount = $shopifyCount + $messagesCount;
        $grossRevenue = (clone $baseQuery)->where('status', 'delivered')->sum('total_price');
        $returnedCount = (clone $baseQuery)->where('status', 'returned')->count();
        $returnsRate = $totalCount > 0 ? ($returnedCount / $totalCount) * 100 : 0;

        return [
            'shopifyCount' => $shopifyCount,
            'messagesCount' => $messagesCount,
            'totalCount' => $totalCount,
            'grossRevenue' => $grossRevenue,
            'returnedCount' => $returnedCount,
            'returnsRate' => $returnsRate,
        ];
    }
}
