<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\ReturnedProduct;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class MonthlyOrdersWidget extends Widget
{
    protected static string $view = 'filament.widgets.monthly-orders-widget';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public function mount(): void
    {
        // Défaut : du 1er octobre 2025 à aujourd'hui pour voir toutes les données
        $this->fromDate = now()->subMonths(6)->startOfMonth()->toDateString();
        $this->toDate = now()->toDateString();
    }

    protected function getViewData(): array
    {
        $start = $this->fromDate
            ? Carbon::parse($this->fromDate)->startOfDay()
            : now()->subMonths(6)->startOfMonth()->startOfDay();
        $end = $this->toDate
            ? Carbon::parse($this->toDate)->endOfDay()
            : now()->endOfDay();

        $baseQuery = Order::whereBetween('order_date', [$start, $end]);

        $deliveredOrders = (clone $baseQuery)
            ->where('status', 'delivered')
            ->get(['source', 'items', 'total_price']);

        $shopifyProducts = 0;
        $messagesProducts = 0;
        $grossRevenue = 0;

        foreach ($deliveredOrders as $order) {
            $quantity = collect($order->items ?? [])->sum(function ($item) {
                return (int) ($item['quantity'] ?? 0);
            });

            if ($order->source === 'shopify') {
                $shopifyProducts += $quantity;
            } else {
                $messagesProducts += $quantity;
            }

            $grossRevenue += (float) $order->total_price;
        }

        $totalProducts = $shopifyProducts + $messagesProducts;
        $returnedProductsCount = (int) ReturnedProduct::whereHas('order', function ($query) use ($start, $end) {
            $query->whereBetween('order_date', [$start, $end]);
        })->sum('quantity');

        $returnsRate = $totalProducts > 0 ? ($returnedProductsCount / $totalProducts) * 100 : 0;

        return [
            'fromDate' => $start->toDateString(),
            'toDate' => $end->toDateString(),
            'shopifyCount' => $shopifyProducts,
            'messagesCount' => $messagesProducts,
            'totalCount' => $totalProducts,
            'grossRevenue' => $grossRevenue,
            'returnedCount' => $returnedProductsCount,
            'returnsRate' => $returnsRate,
        ];
    }
}
